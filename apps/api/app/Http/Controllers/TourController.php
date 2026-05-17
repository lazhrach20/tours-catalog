<?php

namespace App\Http\Controllers;

use App\Enums\TourStatus;
use App\Exceptions\EmbeddingsUnavailableException;
use App\Http\Requests\TourIndexRequest;
use App\Http\Resources\TourCardResource;
use App\Http\Resources\TourDetailResource;
use App\Models\Tour;
use App\Services\Search\FulltextSearchService;
use App\Services\Search\SemanticSearchService;
use App\Services\TourCatalogSorter;
use App\Services\TourListFilter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
    public function __construct(
        private TourListFilter $tourListFilter,
        private TourCatalogSorter $tourCatalogSorter,
        private SemanticSearchService $semanticSearchService,
        private FulltextSearchService $fulltextSearchService,
    ) {}

    public function index(TourIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $searchQuery = $request->searchQuery();

        if ($searchQuery !== '') {
            return $this->searchIndex($request, $filters, $searchQuery);
        }

        $query = $this->tourListFilter->apply(Tour::query(), $filters);
        $this->tourCatalogSorter->apply($query, $request->sort());

        $paginator = $query
            ->with($this->listRelations())
            ->paginate(
                perPage: $request->perPage(),
                page: $request->page(),
            );

        return TourCardResource::collection($paginator);
    }

    public function show(string $slug): TourDetailResource
    {
        $tour = Tour::query()
            ->where('slug', $slug)
            ->with([
                'categories',
                'photos',
                'routePoints',
                'departures',
            ])
            ->first();

        abort_if(
            $tour === null || $tour->status !== TourStatus::Published,
            404,
            'Tour not found.',
        );

        return new TourDetailResource($tour);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function searchIndex(
        TourIndexRequest $request,
        array $filters,
        string $searchQuery,
    ): AnonymousResourceCollection {
        $rankedIds = $this->resolveSearchIds($searchQuery, $filters);

        if ($rankedIds === []) {
            return TourCardResource::collection(
                new LengthAwarePaginator([], 0, $request->perPage(), $request->page()),
            );
        }

        $page = $request->page();
        $perPage = $request->perPage();
        $total = count($rankedIds);
        $offset = ($page - 1) * $perPage;
        $pageIds = array_slice($rankedIds, $offset, $perPage);

        $tours = Tour::query()
            ->with($this->listRelations())
            ->whereIn('id', $pageIds)
            ->get();

        $ordered = collect($pageIds)
            ->map(static fn (int $id) => $tours->firstWhere('id', $id))
            ->filter()
            ->values();

        $paginator = new LengthAwarePaginator(
            $ordered,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return TourCardResource::collection($paginator);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<int>
     */
    private function resolveSearchIds(string $searchQuery, array $filters): array
    {
        try {
            return $this->semanticSearchService->search($searchQuery, $filters);
        } catch (EmbeddingsUnavailableException $exception) {
            Log::warning('Semantic search unavailable, falling back to fulltext.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->fulltextSearchService->search($searchQuery, $filters);
        }
    }

    /**
     * @return list<string>
     */
    private function listRelations(): array
    {
        return [
            'categories',
            'photos',
            'departures',
        ];
    }
}
