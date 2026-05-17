<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplaceRoutePointsRequest;
use App\Models\RoutePoint;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminRouteController extends Controller
{
    public function replace(ReplaceRoutePointsRequest $request, Tour $tour): JsonResponse
    {
        $points = collect($request->validated('points'))
            ->sortBy('sort_order')
            ->values();

        DB::transaction(function () use ($tour, $points): void {
            $tour->routePoints()->delete();

            foreach ($points as $index => $point) {
                RoutePoint::query()->create([
                    'tour_id' => $tour->id,
                    'title' => $point['title'],
                    'lat' => $point['lat'],
                    'lng' => $point['lng'],
                    'sort_order' => $point['sort_order'] ?? $index,
                ]);
            }
        });

        $tour->load('routePoints');

        return response()->json([
            'message' => 'Route points replaced successfully.',
            'route_points' => $tour->routePoints->map(fn (RoutePoint $point) => [
                'id' => $point->id,
                'title' => $point->title,
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
                'sort_order' => $point->sort_order,
            ])->values(),
        ]);
    }
}
