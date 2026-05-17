<?php

namespace App\Services\Embeddings;

use App\Models\Tour;
use App\Support\VectorFormatter;
use Illuminate\Support\Facades\DB;

class TourEmbeddingService
{
    public function __construct(
        private EmbeddingsClient $embeddingsClient,
    ) {}

    public function index(Tour $tour): void
    {
        $text = $this->buildText($tour);
        if ($text === '') {
            return;
        }

        $vector = $this->embeddingsClient->embed($text);

        DB::update(
            'UPDATE tours SET embedding = ?::vector, updated_at = ? WHERE id = ?',
            [
                VectorFormatter::toPgVector($vector),
                now(),
                $tour->id,
            ],
        );
    }

    /**
     * @param  list<string>  $changedFields
     */
    public function syncIfTextChanged(Tour $tour, array $changedFields): void
    {
        $textFields = ['title', 'short_description', 'full_description'];
        if (array_intersect($textFields, $changedFields) === []) {
            return;
        }

        try {
            $this->index($tour);
        } catch (\Throwable) {
            // Embedding failures must not block admin saves.
        }
    }

    public function buildText(Tour $tour): string
    {
        return trim(implode("\n\n", array_filter([
            $tour->title,
            $tour->short_description,
            $tour->full_description,
        ])));
    }
}
