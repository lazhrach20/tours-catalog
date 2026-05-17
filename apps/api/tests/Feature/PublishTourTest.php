<?php

namespace Tests\Feature;

use App\Enums\TourStatus;
use App\Models\RoutePoint;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\TourPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublishTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_returns_422_when_requirements_missing(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tour = Tour::query()->create([
            'slug' => 'incomplete-tour',
            'title' => 'Incomplete Tour',
            'short_description' => '',
            'full_description' => '',
            'duration_days' => 2,
            'status' => TourStatus::Draft,
        ]);

        $this->postJson("/api/v1/admin/tours/{$tour->id}/publish")
            ->assertStatus(422)
            ->assertJsonPath('code', 'TOUR_PUBLISH_INVALID')
            ->assertJsonStructure(['message', 'errors', 'code']);
    }

    public function test_publish_succeeds_when_tour_meets_requirements(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tour = Tour::query()->create([
            'slug' => 'ready-tour',
            'title' => 'Ready Tour',
            'short_description' => 'Short description for catalog.',
            'full_description' => 'Full description with itinerary details.',
            'duration_days' => 5,
            'status' => TourStatus::Draft,
        ]);

        TourPhoto::query()->create([
            'tour_id' => $tour->id,
            'path' => 'tours/ready/cover.jpg',
            'alt' => 'Cover',
            'sort_order' => 0,
            'is_cover' => true,
        ]);

        RoutePoint::query()->create([
            'tour_id' => $tour->id,
            'title' => 'Start',
            'lat' => 55.75,
            'lng' => 37.62,
            'sort_order' => 0,
        ]);

        RoutePoint::query()->create([
            'tour_id' => $tour->id,
            'title' => 'Finish',
            'lat' => 55.80,
            'lng' => 37.70,
            'sort_order' => 1,
        ]);

        TourDeparture::query()->create([
            'tour_id' => $tour->id,
            'start_date' => Carbon::now()->addWeek()->toDateString(),
            'price' => 25000,
            'currency' => 'RUB',
        ]);

        $this->postJson("/api/v1/admin/tours/{$tour->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', TourStatus::Published->value);

        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'status' => TourStatus::Published->value,
        ]);
    }
}
