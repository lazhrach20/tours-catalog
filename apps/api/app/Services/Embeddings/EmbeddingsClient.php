<?php

namespace App\Services\Embeddings;

use App\Exceptions\EmbeddingsUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingsClient
{
    /**
     * @return list<float>
     */
    public function embed(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            throw new EmbeddingsUnavailableException('Embedding text must not be empty.');
        }

        $url = config('embeddings.url').'/embed';
        $timeout = config('embeddings.timeout');

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($url, ['text' => $text]);
        } catch (\Throwable $exception) {
            Log::warning('Embeddings service request failed.', [
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            throw new EmbeddingsUnavailableException(
                'Embeddings service is unavailable.',
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            Log::warning('Embeddings service returned an error.', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new EmbeddingsUnavailableException(
                'Embeddings service returned HTTP '.$response->status().'.',
            );
        }

        $vector = $response->json('vector');

        if (! is_array($vector) || $vector === []) {
            throw new EmbeddingsUnavailableException('Embeddings service returned an invalid vector.');
        }

        return array_map(static fn ($value): float => (float) $value, $vector);
    }
}
