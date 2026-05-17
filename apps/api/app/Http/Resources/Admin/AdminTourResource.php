<?php

namespace App\Http\Resources\Admin;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tour */
class AdminTourResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'duration_days' => $this->duration_days,
            'duration_nights' => $this->duration_nights,
            'status' => $this->status->value,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'published_at' => $this->published_at?->toIso8601String(),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'color' => $category->color,
            ])),
            'photos' => $this->whenLoaded('photos', fn () => $this->photos->map(fn ($photo) => [
                'id' => $photo->id,
                'path' => $photo->path,
                'url' => MediaUrl::publicPath($photo->path),
                'alt' => $photo->alt,
                'sort_order' => $photo->sort_order,
                'is_cover' => $photo->is_cover,
            ])),
            'route_points' => $this->whenLoaded('routePoints', fn () => $this->routePoints->map(fn ($point) => [
                'id' => $point->id,
                'title' => $point->title,
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
                'sort_order' => $point->sort_order,
            ])),
            'departures' => $this->whenLoaded('departures', fn () => $this->departures->map(fn ($departure) => [
                'id' => $departure->id,
                'start_date' => $departure->start_date?->toDateString(),
                'end_date' => $departure->end_date?->toDateString(),
                'price' => $departure->price,
                'currency' => $departure->currency,
                'seats' => $departure->seats,
            ])),
        ];
    }
}
