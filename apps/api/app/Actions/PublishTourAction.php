<?php

namespace App\Actions;

use App\Enums\TourStatus;
use App\Exceptions\PublishTourException;
use App\Models\Tour;
use Illuminate\Support\Carbon;

class PublishTourAction
{
    public function execute(Tour $tour): Tour
    {
        $tour->load(['photos', 'routePoints', 'departures']);

        $errors = [];

        if (! $tour->photos->contains('is_cover', true)) {
            $errors['cover'] = ['A cover photo is required before publishing.'];
        }

        if (trim((string) $tour->short_description) === '') {
            $errors['short_description'] = ['Short description is required before publishing.'];
        }

        if (trim((string) $tour->full_description) === '') {
            $errors['full_description'] = ['Full description is required before publishing.'];
        }

        $hasFutureDeparture = $tour->departures->contains(
            fn ($departure) => Carbon::parse($departure->start_date)->startOfDay()->gte(now()->startOfDay()),
        );

        if (! $hasFutureDeparture) {
            $errors['departures'] = ['At least one future departure date is required before publishing.'];
        }

        if ($tour->routePoints->count() < 2) {
            $errors['route_points'] = ['At least two route points are required before publishing.'];
        }

        if ($errors !== []) {
            throw new PublishTourException('Tour cannot be published.', $errors);
        }

        $tour->update([
            'status' => TourStatus::Published,
            'published_at' => now(),
            'archived_at' => null,
        ]);

        return $tour->fresh();
    }
}
