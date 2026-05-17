<?php

namespace Tests\Feature;

use App\Enums\TourStatus;
use App\Models\Tour;
use App\Support\VectorFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MocksEmbeddingsHttp;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    use MocksEmbeddingsHttp;
    use RefreshDatabase;

    public function test_semantic_search_uses_embeddings_when_available(): void
    {
        $vector = $this->sampleEmbeddingVector();
        $this->fakeEmbeddingsHttp($vector);

        $tour = Tour::query()->create([
            'slug' => 'semantic-match',
            'title' => 'Горный поход',
            'short_description' => 'Альпийские тропы',
            'full_description' => 'Маршрут по хребтам.',
            'duration_days' => 6,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);

        DB::update(
            'UPDATE tours SET embedding = ?::vector WHERE id = ?',
            [VectorFormatter::toPgVector($vector), $tour->id],
        );

        Tour::query()->create([
            'slug' => 'other-tour',
            'title' => 'Морской круиз',
            'short_description' => 'Пляжный отдых',
            'full_description' => 'Океан и солнце.',
            'duration_days' => 4,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/tours?q=горы')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'semantic-match');
    }

    public function test_semantic_search_falls_back_to_fulltext_when_embeddings_unavailable(): void
    {
        $this->fakeEmbeddingsHttpUnavailable();

        Tour::query()->create([
            'slug' => 'fulltext-match',
            'title' => 'Пешеходный поход в горы',
            'short_description' => 'Треккинг для начинающих',
            'full_description' => 'Подробное описание маршрута.',
            'duration_days' => 3,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);

        Tour::query()->create([
            'slug' => 'unrelated-tour',
            'title' => 'Городская экскурсия',
            'short_description' => 'Музеи и парки',
            'full_description' => 'Прогулка по центру.',
            'duration_days' => 1,
            'status' => TourStatus::Published,
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/tours?q=горы')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'fulltext-match');
    }
}
