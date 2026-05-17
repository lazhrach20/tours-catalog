<?php

namespace App\Services;

use App\Enums\TourStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TourListFilter
{
    /**
     * @param  array{
     *   categories?: list<string>,
     *   minDays?: int,
     *   maxDays?: int,
     *   priceMin?: int,
     *   priceMax?: int,
     *   dateFrom?: string,
     *   dateTo?: string,
     * }  $filters
     */
    public function apply(Builder $query, array $filters = []): Builder
    {
        $query->where('status', TourStatus::Published);

        if (! empty($filters['categories'])) {
            $slugs = $filters['categories'];
            $query->whereHas('categories', function (Builder $categories) use ($slugs): void {
                $categories->whereIn('slug', $slugs);
            });
        }

        if (isset($filters['minDays'])) {
            $query->where('duration_days', '>=', $filters['minDays']);
        }

        if (isset($filters['maxDays'])) {
            $query->where('duration_days', '<=', $filters['maxDays']);
        }

        if (isset($filters['priceMin']) || isset($filters['priceMax'])) {
            $query->whereRaw($this->minFuturePriceSql().' IS NOT NULL');

            if (isset($filters['priceMin'])) {
                $query->whereRaw($this->minFuturePriceSql().' >= ?', [$filters['priceMin']]);
            }

            if (isset($filters['priceMax'])) {
                $query->whereRaw($this->minFuturePriceSql().' <= ?', [$filters['priceMax']]);
            }
        }

        if (! empty($filters['dateFrom']) || ! empty($filters['dateTo'])) {
            $dateFrom = ! empty($filters['dateFrom'])
                ? Carbon::parse($filters['dateFrom'])->toDateString()
                : null;
            $dateTo = ! empty($filters['dateTo'])
                ? Carbon::parse($filters['dateTo'])->toDateString()
                : null;

            $query->whereHas('departures', function (Builder $departures) use ($dateFrom, $dateTo): void {
                $departures->whereDate('start_date', '>=', now()->toDateString());

                if ($dateFrom !== null) {
                    $departures->whereRaw(
                        'COALESCE(end_date, start_date) >= ?',
                        [$dateFrom],
                    );
                }

                if ($dateTo !== null) {
                    $departures->whereDate('start_date', '<=', $dateTo);
                }
            });
        }

        return $query;
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
}
