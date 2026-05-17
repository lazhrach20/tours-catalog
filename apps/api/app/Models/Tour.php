<?php

namespace App\Models;

use App\Enums\TourStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'short_description',
        'full_description',
        'duration_days',
        'duration_nights',
        'status',
        'meta_title',
        'meta_description',
        'published_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TourStatus::class,
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'tour_category');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TourPhoto::class)->orderBy('sort_order');
    }

    public function routePoints(): HasMany
    {
        return $this->hasMany(RoutePoint::class)->orderBy('sort_order');
    }

    public function departures(): HasMany
    {
        return $this->hasMany(TourDeparture::class)->orderBy('start_date');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TourStatus::Published);
    }

    public function scopeWithFutureDepartures(Builder $query): Builder
    {
        return $query->whereHas('departures', function (Builder $departures): void {
            $departures->whereDate('start_date', '>=', now()->toDateString());
        });
    }
}
