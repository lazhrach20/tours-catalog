<?php

namespace App\Services\Llm;

use App\Exceptions\LlmUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function chatJson(array $messages): array
    {
        $apiKey = config('llm.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            throw new LlmUnavailableException('LLM API key is not configured.', 'LLM_NOT_CONFIGURED');
        }

        $url = config('llm.base_url').'/chat/completions';

        try {
            $response = Http::timeout(config('llm.timeout'))
                ->withToken($apiKey)
                ->acceptJson()
                ->post($url, [
                    'model' => config('llm.model'),
                    'messages' => $messages,
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.7,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('LLM request failed.', ['message' => $exception->getMessage()]);

            throw new LlmUnavailableException(previous: $exception);
        }

        if (! $response->successful()) {
            Log::warning('LLM returned an error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new LlmUnavailableException(
                'LLM service returned HTTP '.$response->status().'.',
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new LlmUnavailableException('LLM returned an empty response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new LlmUnavailableException('LLM returned invalid JSON.');
        }

        return $decoded;
    }
}
