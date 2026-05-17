<?php

namespace Tests\Feature;

use App\Enums\TourStatus;
use App\Models\Category;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'INVALID_CREDENTIALS');
    }

    public function test_admin_can_access_tours_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Tour::query()->create([
            'slug' => 'test-tour',
            'title' => 'Test Tour',
            'short_description' => 'Short',
            'full_description' => 'Full',
            'duration_days' => 3,
            'status' => TourStatus::Draft,
        ]);

        $this->getJson('/api/v1/admin/tours')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Test Tour');
    }

    public function test_category_delete_returns_conflict_when_in_use(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::query()->create([
            'name' => 'Mountains',
            'slug' => 'mountains',
        ]);

        $tour = Tour::query()->create([
            'slug' => 'linked-tour',
            'title' => 'Linked Tour',
            'short_description' => 'Short',
            'full_description' => 'Full',
            'duration_days' => 2,
            'status' => TourStatus::Draft,
        ]);

        $tour->categories()->attach($category->id);

        $this->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertStatus(409)
            ->assertJsonPath('code', 'CATEGORY_IN_USE');
    }
}
