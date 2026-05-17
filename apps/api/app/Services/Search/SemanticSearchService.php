<?php

namespace App\Services\Search;

use App\Exceptions\EmbeddingsUnavailableException;
use App\Services\Embeddings\EmbeddingsClient;
use App\Services\TourListFilter;
use App\Support\VectorFormatter;
use Illuminate\Support\Facades\DB;

class SemanticSearchService
{
    public function __construct(
        private EmbeddingsClient $embeddingsClient,
        private TourListFilter $tourListFilter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return list<int>
     *
     * @throws EmbeddingsUnavailableException
     */
    public function search(string $query, array $filters = []): array
    {
        $vector = $this->embeddingsClient->embed($query);
        $vectorLiteral = VectorFormatter::toPgVector($vector);
        $minScore = config('search.min_score');
        $topK = config('search.top_k');
        $maxDistance = 1 - $minScore;

        $filteredIds = $this->filteredTourIds($filters);

        if ($filteredIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($filteredIds), '?'));

        $rows = DB::select(
            <<<SQL
                SELECT id
                FROM tours
                WHERE id IN ({$placeholders})
                  AND embedding IS NOT NULL
                  AND (embedding <=> ?::vector) <= ?
                ORDER BY embedding <=> ?::vector
                LIMIT ?
            SQL,
            [
                ...$filteredIds,
                $vectorLiteral,
                $maxDistance,
                $vectorLiteral,
                $topK,
            ],
        );

        return array_map(static fn (object $row): int => (int) $row->id, $rows);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<int>
     */
    private function filteredTourIds(array $filters): array
    {
        $builder = $this->tourListFilter->apply(
            \App\Models\Tour::query(),
            $filters,
        );

        return $builder->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }
}
