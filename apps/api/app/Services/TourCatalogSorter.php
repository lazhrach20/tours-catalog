<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class TourCatalogSorter
{
    public function apply(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderByRaw($this->minFuturePriceSql().' ASC NULLS LAST'),
            'duration' => $query->orderBy('duration_days')->orderBy('id'),
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            default => $query->orderByRaw($this->nearestDepartureSql().' ASC NULLS LAST')->orderBy('id'),
        };
    }

    private function minFuturePriceSql(): string
    {
        return <<<'SQL'
            (
                SELECT MIN(price)
                FROM tour_departures
                WHERE tour_departures.tour_id = tours.id
                  AND tour_departures.start_date >= CURRENT_DATE
            )
        SQL;
    }

    private function nearestDepartureSql(): string
    {
        return <<<'SQL'
            (
                SELECT MIN(start_date)
                FROM tour_departures
                WHERE tour_departures.tour_id = tours.id
                  AND tour_departures.start_date >= CURRENT_DATE
            )
        SQL;
    }
}
