<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin \App\Models\Tour */
class TourCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $futureDepartures = $this->departures
            ->filter(static fn ($departure): bool => $departure->start_date->toDateString() >= now()->toDateString())
            ->sortBy('start_date')
            ->values();

        $minPriceDeparture = $futureDepartures->sortBy('price')->first();
        $nextDeparture = $futureDepartures->first();

        $cover = $this->photos->firstWhere('is_cover', true) ?? $this->photos->first();

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'coverUrl' => MediaUrl::publicPath($cover?->path),
            'categories' => $this->categories->map(static fn ($category): array => [
                'slug' => $category->slug,
                'name' => $category->name,
                'color' => $category->color,
            ])->values()->all(),
            'duration' => [
                'days' => $this->duration_days,
                'nights' => $this->duration_nights,
            ],
            'fromPrice' => $minPriceDeparture !== null
                ? MoneyFormatter::format($minPriceDeparture->price)
                : null,
            'currency' => $minPriceDeparture?->currency ?? 'RUB',
            'nextDepartureDate' => $nextDeparture?->start_date instanceof Carbon
                ? $nextDeparture->start_date->toDateString()
                : null,
        ];
    }
}
