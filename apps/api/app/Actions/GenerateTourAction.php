<?php

namespace App\Actions;

use App\Enums\TourStatus;
use App\Exceptions\LlmUnavailableException;
use App\Models\Category;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Str;

class GenerateTourAction
{
    public function __construct(
        private LlmClient $llmClient,
    ) {}

    /**
     * @param  array{region?: string, type?: string, duration_days?: int, notes?: string}  $prompt
     * @return array<string, mixed>
     */
    public function execute(array $prompt): array
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $categoryList = $categories
            ->map(fn ($c) => "{$c->slug} ({$c->name})")
            ->implode(', ');

        $userPrompt = collect([
            $prompt['region'] ?? null ? 'Регион: '.$prompt['region'] : null,
            $prompt['type'] ?? null ? 'Тип отдыха: '.$prompt['type'] : null,
            isset($prompt['duration_days']) ? 'Длительность (дней): '.$prompt['duration_days'] : null,
            $prompt['notes'] ?? null ? 'Пожелания: '.$prompt['notes'] : null,
        ])->filter()->implode("\n");

        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты помощник турагентства. Отвечай только валидным JSON без markdown. '
                    .'Поля: title (string), short_description (string, до 300 символов), '
                    .'full_description (string, markdown), duration_days (int), duration_nights (int|null), '
                    .'meta_title (string), meta_description (string), category_slugs (array of slug strings). '
                    .'Выбирай category_slugs только из списка: '.$categoryList.'.',
            ],
            [
                'role' => 'user',
                'content' => $userPrompt !== '' ? $userPrompt : 'Сгенерируй интересный тур по России.',
            ],
        ];

        try {
            $payload = $this->llmClient->chatJson($messages);
        } catch (LlmUnavailableException $e) {
            throw $e;
        }

        $durationDays = max(1, (int) ($payload['duration_days'] ?? $prompt['duration_days'] ?? 5));
        $durationNights = isset($payload['duration_nights'])
            ? max(0, (int) $payload['duration_nights'])
            : max(0, $durationDays - 1);

        $title = trim((string) ($payload['title'] ?? 'Новый тур'));
        if ($title === '') {
            $title = 'Новый тур';
        }

        $slugSlugs = collect($payload['category_slugs'] ?? [])
            ->filter(fn ($s) => is_string($s) && $s !== '')
            ->map(fn (string $s) => Str::slug($s))
            ->all();

        $categoryIds = $categories
            ->filter(fn ($c) => in_array($c->slug, $slugSlugs, true))
            ->pluck('id')
            ->values()
            ->all();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => trim((string) ($payload['short_description'] ?? '')),
            'full_description' => trim((string) ($payload['full_description'] ?? '')),
            'duration_days' => $durationDays,
            'duration_nights' => $durationNights,
            'status' => TourStatus::Draft->value,
            'meta_title' => trim((string) ($payload['meta_title'] ?? $title)),
            'meta_description' => trim((string) ($payload['meta_description'] ?? '')),
            'category_ids' => $categoryIds,
        ];
    }
}
