<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

trait MocksEmbeddingsHttp
{
    /**
     * @return list<float>
     */
    protected function sampleEmbeddingVector(int $dimensions = 384): array
    {
        return array_fill(0, $dimensions, 0.25);
    }

    /**
     * @param  list<float>|null  $vector
     */
    protected function fakeEmbeddingsHttp(?array $vector = null): void
    {
        $url = config('embeddings.url').'/embed';

        Http::fake([
            $url => Http::response([
                'vector' => $vector ?? $this->sampleEmbeddingVector(),
            ], 200),
        ]);
    }

    protected function fakeEmbeddingsHttpUnavailable(): void
    {
        $url = config('embeddings.url').'/embed';

        Http::fake([
            $url => Http::response('Embeddings unavailable', 503),
        ]);
    }
}
