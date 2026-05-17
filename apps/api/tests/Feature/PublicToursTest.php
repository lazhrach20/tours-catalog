<?php

namespace Tests\Feature;

use App\Enums\TourStatus;
use App\Models\Category;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicToursTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tour_list_includes_only_published_tours(): void
    {
        Tour::query()->create([
            'slug' => 'published-tour',
            'title' => 'Published Tour',
            'short_description' => 'Visible on storefront.',
            'full_description' => 'Full text.',
            'duration_days' => 3,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);

        Tour::query()->create([
            'slug' => 'draft-tour',
            'title' => 'Draft Tour',
            'short_description' => 'Hidden draft.',
            'full_description' => 'Full text.',
            'duration_days' => 2,
            'status' => TourStatus::Draft,
        ]);

        Tour::query()->create([
            'slug' => 'archived-tour',
            'title' => 'Archived Tour',
            'short_description' => 'Hidden archive.',
            'full_description' => 'Full text.',
            'duration_days' => 4,
            'status' => TourStatus::Archived,
            'archived_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/tours');

        $response->assertOk();

        $slugs = collect($response->json('data'))->pluck('slug')->all();

        $this->assertSame(['published-tour'], $slugs);
    }

    public function test_public_tour_list_filters_by_category_slug(): void
    {
        $mountains = Category::query()->create([
            'name' => 'Mountains',
            'slug' => 'mountains',
        ]);

        $sea = Category::query()->create([
            'name' => 'Sea',
            'slug' => 'sea',
        ]);

        $mountainTour = Tour::query()->create([
            'slug' => 'mountain-tour',
            'title' => 'Mountain Tour',
            'short_description' => 'Peaks and trails.',
            'full_description' => 'Full text.',
            'duration_days' => 5,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);
        $mountainTour->categories()->attach($mountains->id);

        $seaTour = Tour::query()->create([
            'slug' => 'sea-tour',
            'title' => 'Sea Tour',
            'short_description' => 'Coast and beaches.',
            'full_description' => 'Full text.',
            'duration_days' => 3,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);
        $seaTour->categories()->attach($sea->id);

        $this->getJson('/api/v1/tours?categories=mountains')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'mountain-tour')
            ->assertJsonCount(1, 'data');
    }
}
