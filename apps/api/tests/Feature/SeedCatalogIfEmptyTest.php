<?php

namespace Tests\Feature;

use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedCatalogIfEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_when_no_tours_exist(): void
    {
        $this->assertSame(0, Tour::query()->count());

        $this->artisan('catalog:seed-if-empty')
            ->assertSuccessful();

        $this->assertGreaterThan(0, Tour::query()->count());
    }

    public function test_skips_when_tours_already_exist(): void
    {
        $this->artisan('catalog:seed-if-empty')->assertSuccessful();
        $countBefore = Tour::query()->count();

        $this->artisan('catalog:seed-if-empty')
            ->expectsOutputToContain('skipping')
            ->assertSuccessful();

        $this->assertSame($countBefore, Tour::query()->count());
    }
}
