<?php

namespace App\Services\Search;

use App\Models\Tour;
use App\Services\TourListFilter;
use Illuminate\Support\Facades\DB;

class FulltextSearchService
{
    public function __construct(
        private TourListFilter $tourListFilter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return list<int>
     */
    public function search(string $query, array $filters = []): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $filteredIds = $this->tourListFilter->apply(Tour::query(), $filters)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if ($filteredIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($filteredIds), '?'));
        $topK = config('search.top_k');

        $rows = DB::select(
            <<<SQL
                SELECT id,
                       ts_rank(
                           to_tsvector('russian', coalesce(title, '') || ' ' || coalesce(short_description, '')),
                           plainto_tsquery('russian', ?)
                       ) AS rank
                FROM tours
                WHERE id IN ({$placeholders})
                  AND to_tsvector('russian', coalesce(title, '') || ' ' || coalesce(short_description, ''))
                      @@ plainto_tsquery('russian', ?)
                ORDER BY rank DESC
                LIMIT ?
            SQL,
            [
                $query,
                ...$filteredIds,
                $query,
                $topK,
            ],
        );

        return array_map(static fn (object $row): int => (int) $row->id, $rows);
    }
}
