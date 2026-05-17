<?php

namespace App\Http\Resources;

use App\Services\MarkdownSanitizer;
use App\Support\MediaUrl;
use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin \App\Models\Tour */
class TourDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sanitizer = app(MarkdownSanitizer::class);

        $futureDepartures = $this->departures
            ->filter(static fn ($departure): bool => $departure->start_date->toDateString() >= now()->toDateString())
            ->sortBy('start_date')
            ->values();

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'shortDescription' => $this->short_description,
            'descriptionHtml' => $sanitizer->toHtml($this->full_description),
            'duration' => [
                'days' => $this->duration_days,
                'nights' => $this->duration_nights,
            ],
            'categories' => CategoryResource::collection($this->categories),
            'gallery' => $this->photos->map(static fn ($photo): array => [
                'url' => MediaUrl::publicPath($photo->path),
                'alt' => $photo->alt,
                'isCover' => $photo->is_cover,
            ])->values()->all(),
            'departures' => $futureDepartures->map(static fn ($departure): array => [
                'id' => $departure->id,
                'startDate' => $departure->start_date->toDateString(),
                'endDate' => $departure->end_date?->toDateString(),
                'price' => MoneyFormatter::format($departure->price),
                'currency' => $departure->currency,
                'seats' => $departure->seats,
            ])->all(),
            'routePoints' => $this->routePoints->map(static fn ($point): array => [
                'title' => $point->title,
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
                'sortOrder' => $point->sort_order,
            ])->values()->all(),
            'meta' => [
                'title' => $this->meta_title ?? $this->title,
                'description' => $this->meta_description,
                'coverUrl' => MediaUrl::publicPath(
                    ($this->photos->firstWhere('is_cover', true) ?? $this->photos->first())?->path,
                ),
            ],
            'publishedAt' => $this->published_at instanceof Carbon
                ? $this->published_at->toIso8601String()
                : null,
        ];
    }
}
