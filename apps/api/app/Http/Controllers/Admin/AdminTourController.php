<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PublishTourAction;
use App\Enums\TourStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminTourIndexRequest;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Http\Resources\Admin\AdminTourResource;
use App\Models\Tour;
use App\Services\Embeddings\TourEmbeddingService;
use App\Support\SlugGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminTourController extends Controller
{
    public function __construct(
        private TourEmbeddingService $embeddingService,
        private PublishTourAction $publishTourAction,
    ) {}

    public function index(AdminTourIndexRequest $request): AnonymousResourceCollection
    {
        $query = Tour::query()->with('categories');

        if ($request->filled('status')) {
            $query->where('status', $request->validated('status'));
        }

        if ($request->filled('q')) {
            $search = '%'.$request->string('q').'%';
            $query->where('title', 'ilike', $search);
        }

        $tours = $query
            ->orderByDesc('updated_at')
            ->paginate($request->integer('per_page', 20));

        return AdminTourResource::collection($tours);
    }

    public function show(Tour $tour): AdminTourResource
    {
        $tour->load(['categories', 'photos', 'routePoints', 'departures']);

        return new AdminTourResource($tour);
    }

    public function store(StoreTourRequest $request): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $slug = isset($data['slug'])
            ? SlugGenerator::ensureUnique($data['slug'], Tour::class)
            : SlugGenerator::uniqueFromTitle($data['title'], Tour::class);

        $data['slug'] = $slug;
        $data['status'] = $data['status'] ?? TourStatus::Draft;
        $data['short_description'] = $data['short_description'] ?? '';
        $data['full_description'] = $data['full_description'] ?? '';

        $tour = Tour::query()->create($data);
        $tour->categories()->sync($categoryIds);

        $this->embeddingService->syncIfTextChanged($tour, array_keys($data));

        $tour->load(['categories', 'photos', 'routePoints', 'departures']);

        return (new AdminTourResource($tour))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTourRequest $request, Tour $tour): AdminTourResource
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        if (isset($data['slug'])) {
            $data['slug'] = SlugGenerator::ensureUnique($data['slug'], Tour::class, $tour->id);
        }

        $tour->update($data);

        if ($categoryIds !== null) {
            $tour->categories()->sync($categoryIds);
        }

        $this->embeddingService->syncIfTextChanged($tour->fresh(), array_keys($data));

        $tour->load(['categories', 'photos', 'routePoints', 'departures']);

        return new AdminTourResource($tour);
    }

    public function destroy(Tour $tour): JsonResponse
    {
        $tour->update([
            'status' => TourStatus::Archived,
            'archived_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tour archived successfully.',
        ]);
    }

    public function publish(Tour $tour): AdminTourResource
    {
        $published = $this->publishTourAction->execute($tour);
        $published->load(['categories', 'photos', 'routePoints', 'departures']);

        return new AdminTourResource($published);
    }
}
