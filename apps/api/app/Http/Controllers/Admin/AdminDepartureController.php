<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartureRequest;
use App\Models\Tour;
use App\Models\TourDeparture;
use Illuminate\Http\JsonResponse;

class AdminDepartureController extends Controller
{
    public function index(Tour $tour): JsonResponse
    {
        return response()->json([
            'data' => $tour->departures()
                ->orderBy('start_date')
                ->get()
                ->map(fn (TourDeparture $departure) => $this->departurePayload($departure))
                ->values(),
        ]);
    }

    public function store(DepartureRequest $request, Tour $tour): JsonResponse
    {
        $departure = $tour->departures()->create($request->validated());

        return response()->json([
            'message' => 'Departure created successfully.',
            'data' => $this->departurePayload($departure),
        ], 201);
    }

    public function update(DepartureRequest $request, Tour $tour, TourDeparture $departure): JsonResponse
    {
        $this->ensureBelongsToTour($tour, $departure);

        $departure->update($request->validated());

        return response()->json([
            'message' => 'Departure updated successfully.',
            'data' => $this->departurePayload($departure->fresh()),
        ]);
    }

    public function destroy(Tour $tour, TourDeparture $departure): JsonResponse
    {
        $this->ensureBelongsToTour($tour, $departure);

        $departure->delete();

        return response()->json([
            'message' => 'Departure deleted successfully.',
        ]);
    }

    private function ensureBelongsToTour(Tour $tour, TourDeparture $departure): void
    {
        if ($departure->tour_id !== $tour->id) {
            abort(404, 'Departure not found for this tour.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function departurePayload(TourDeparture $departure): array
    {
        return [
            'id' => $departure->id,
            'start_date' => $departure->start_date?->toDateString(),
            'end_date' => $departure->end_date?->toDateString(),
            'price' => $departure->price,
            'currency' => $departure->currency,
            'seats' => $departure->seats,
        ];
    }
}
