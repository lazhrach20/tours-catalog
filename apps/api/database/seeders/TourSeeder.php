<?php

namespace Database\Seeders;

use App\Enums\TourStatus;
use App\Models\Category;
use App\Models\RoutePoint;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\TourPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $tours = $this->tourDefinitions();

        foreach ($tours as $definition) {
            $status = TourStatus::from($definition['status']);
            $categorySlugs = $definition['categories'];
            $photos = $definition['photos'] ?? [];
            $routePoints = $definition['route_points'] ?? [];
            $departures = $definition['departures'] ?? [];

            unset(
                $definition['categories'],
                $definition['photos'],
                $definition['route_points'],
                $definition['departures'],
            );

            $tour = Tour::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                array_merge($definition, [
                    'status' => $status,
                    'published_at' => $status === TourStatus::Published
                        ? ($definition['published_at'] ?? now()->subDays(3))
                        : null,
                    'archived_at' => $status === TourStatus::Archived
                        ? ($definition['archived_at'] ?? now()->subDay())
                        : null,
                ]),
            );

            $tour->categories()->sync(
                collect($categorySlugs)
                    ->map(fn (string $slug) => $categories[$slug] ?? null)
                    ->filter()
                    ->values()
                    ->all(),
            );

            $tour->photos()->delete();
            $tour->routePoints()->delete();
            $tour->departures()->delete();

            if ($status !== TourStatus::Published) {
                continue;
            }

            foreach ($photos as $index => $photo) {
                TourPhoto::query()->create([
                    'tour_id' => $tour->id,
                    'path' => $this->seedPhotoUrl($tour->slug, $index),
                    'alt' => $photo['alt'],
                    'sort_order' => $index,
                    'is_cover' => $index === 0,
                ]);
            }

            foreach ($routePoints as $index => $point) {
                RoutePoint::query()->create([
                    'tour_id' => $tour->id,
                    'title' => $point['title'],
                    'lat' => $point['lat'],
                    'lng' => $point['lng'],
                    'sort_order' => $index,
                ]);
            }

            foreach ($departures as $departure) {
                TourDeparture::query()->create([
                    'tour_id' => $tour->id,
                    'start_date' => $departure['start_date'],
                    'end_date' => $departure['end_date'] ?? null,
                    'price' => $departure['price'],
                    'currency' => $departure['currency'] ?? 'RUB',
                    'seats' => $departure['seats'] ?? null,
                ]);
            }
        }
    }

    private function seedPhotoUrl(string $slug, int $index): string
    {
        return sprintf('https://picsum.photos/seed/%s-%d/800/600', $slug, $index + 1);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tourDefinitions(): array
    {
        return [
            [
                'slug' => 'baikal-winter',
                'title' => 'Зимний Байкал',
                'short_description' => 'Лёд, остров Ольхон и термальные источники за 7 дней.',
                'full_description' => 'Маршрут по зимнему Байкалу с проживанием в Иркутске и на Ольхоне.',
                'duration_days' => 7,
                'duration_nights' => 6,
                'status' => TourStatus::Published->value,
                'meta_title' => 'Зимний Байкал — тур',
                'meta_description' => 'Зимний тур на Байкал с маршрутом по льду.',
                'categories' => ['mountains', 'winter'],
                'photos' => [
                    ['path' => 'seed/tours/baikal-winter-1.jpg', 'alt' => 'Лёд Байкала'],
                    ['path' => 'seed/tours/baikal-winter-2.jpg', 'alt' => 'Ольхон'],
                ],
                'route_points' => [
                    ['title' => 'Иркутск', 'lat' => 52.286974, 'lng' => 104.305018],
                    ['title' => 'Ольхон', 'lat' => 53.149700, 'lng' => 107.333300],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(21)->toDateString(), 'end_date' => Carbon::now()->addDays(28)->toDateString(), 'price' => 89000, 'seats' => 12],
                    ['start_date' => Carbon::now()->addDays(49)->toDateString(), 'end_date' => Carbon::now()->addDays(56)->toDateString(), 'price' => 92000, 'seats' => 10],
                ],
            ],
            [
                'slug' => 'kamchatka-volcanoes',
                'title' => 'Вулканы Камчатки',
                'short_description' => 'Треккинг к вулканам и горячие источники.',
                'full_description' => 'Активный тур по Камчатке с акцентом на вулканы и дикую природу.',
                'duration_days' => 10,
                'duration_nights' => 9,
                'status' => TourStatus::Published->value,
                'categories' => ['mountains', 'adventure'],
                'photos' => [
                    ['path' => 'seed/tours/kamchatka-1.jpg', 'alt' => 'Вулкан'],
                    ['path' => 'seed/tours/kamchatka-2.jpg', 'alt' => 'Долина гейзеров'],
                ],
                'route_points' => [
                    ['title' => 'Петропавловск-Камчатский', 'lat' => 53.024265, 'lng' => 158.643898],
                    ['title' => 'Авачинская сопка', 'lat' => 53.255000, 'lng' => 158.830000],
                    ['title' => 'Долина гейзеров', 'lat' => 54.430000, 'lng' => 160.140000],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(35)->toDateString(), 'price' => 145000, 'seats' => 8],
                ],
            ],
            [
                'slug' => 'sochi-sea-coast',
                'title' => 'Черноморское побережье Сочи',
                'short_description' => 'Пляжи, каньоны и винодельни Краснодарского края.',
                'full_description' => 'Спокойный тур по побережью с экскурсиями и свободным временем.',
                'duration_days' => 5,
                'duration_nights' => 4,
                'status' => TourStatus::Published->value,
                'categories' => ['sea'],
                'photos' => [
                    ['path' => 'seed/tours/sochi-1.jpg', 'alt' => 'Пляж'],
                ],
                'route_points' => [
                    ['title' => 'Сочи', 'lat' => 43.585472, 'lng' => 39.723098],
                    ['title' => 'Красная Поляна', 'lat' => 43.679097, 'lng' => 40.205080],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(14)->toDateString(), 'price' => 52000, 'seats' => 16],
                    ['start_date' => Carbon::now()->addDays(42)->toDateString(), 'price' => 54000, 'seats' => 14],
                ],
            ],
            [
                'slug' => 'golden-ring',
                'title' => 'Золотое кольцо',
                'short_description' => 'Сергиев Посад, Суздаль и Владимир за 4 дня.',
                'full_description' => 'Классический маршрут по древним городам центральной России.',
                'duration_days' => 4,
                'duration_nights' => 3,
                'status' => TourStatus::Published->value,
                'categories' => ['cities', 'culture'],
                'photos' => [
                    ['path' => 'seed/tours/golden-ring-1.jpg', 'alt' => 'Суздаль'],
                    ['path' => 'seed/tours/golden-ring-2.jpg', 'alt' => 'Собор'],
                ],
                'route_points' => [
                    ['title' => 'Сергиев Посад', 'lat' => 56.306351, 'lng' => 38.150236],
                    ['title' => 'Суздаль', 'lat' => 56.419836, 'lng' => 40.448826],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(10)->toDateString(), 'price' => 38000, 'seats' => 20],
                ],
            ],
            [
                'slug' => 'altai-eco-trail',
                'title' => 'Эко-тропа Алтая',
                'short_description' => 'Треккинг, чайные поляны и этнографические деревни.',
                'full_description' => 'Экологичный маршрут с минимальным следом и локальными гидами.',
                'duration_days' => 8,
                'duration_nights' => 7,
                'status' => TourStatus::Published->value,
                'categories' => ['eco', 'mountains'],
                'photos' => [
                    ['path' => 'seed/tours/altai-1.jpg', 'alt' => 'Горы Алтая'],
                ],
                'route_points' => [
                    ['title' => 'Горно-Алтайск', 'lat' => 51.958056, 'lng' => 85.960278],
                    ['title' => 'Телецкое озеро', 'lat' => 51.500000, 'lng' => 87.700000],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(28)->toDateString(), 'price' => 76000, 'seats' => 10],
                ],
            ],
            [
                'slug' => 'kazan-city-break',
                'title' => 'Казань за выходные',
                'short_description' => 'Кремль, улица Баумана и татарская кухня.',
                'full_description' => 'Короткий городской тур с гастрономическими остановками.',
                'duration_days' => 3,
                'duration_nights' => 2,
                'status' => TourStatus::Published->value,
                'categories' => ['cities', 'culture'],
                'photos' => [
                    ['path' => 'seed/tours/kazan-1.jpg', 'alt' => 'Кремль'],
                ],
                'route_points' => [
                    ['title' => 'Казанский Кремль', 'lat' => 55.798551, 'lng' => 49.106324],
                    ['title' => 'Улица Баумана', 'lat' => 55.788700, 'lng' => 49.122500],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(7)->toDateString(), 'price' => 29000, 'seats' => 18],
                ],
            ],
            [
                'slug' => 'karelia-lakes',
                'title' => 'Озёра Карелии',
                'short_description' => 'Кижи, Рускеала и белые ночи.',
                'full_description' => 'Северный маршрут с паромами и прогулками по озёрам.',
                'duration_days' => 6,
                'duration_nights' => 5,
                'status' => TourStatus::Published->value,
                'categories' => ['eco', 'culture'],
                'photos' => [
                    ['path' => 'seed/tours/karelia-1.jpg', 'alt' => 'Рускеала'],
                ],
                'route_points' => [
                    ['title' => 'Петрозаводск', 'lat' => 61.785021, 'lng' => 34.346878],
                    ['title' => 'Остров Кижи', 'lat' => 62.066667, 'lng' => 35.225000],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(18)->toDateString(), 'price' => 61000, 'seats' => 12],
                ],
            ],
            [
                'slug' => 'elbrus-summit-prep',
                'title' => 'Подготовка к Эльбрусу',
                'short_description' => 'Акклиматизация и тренировочные восхождения.',
                'full_description' => 'Черновик тура для альпинистов — публикация после доработки маршрута.',
                'duration_days' => 9,
                'duration_nights' => 8,
                'status' => TourStatus::Draft->value,
                'categories' => ['mountains', 'adventure'],
            ],
            [
                'slug' => 'crimea-spring-draft',
                'title' => 'Весенний Крым',
                'short_description' => 'Черновик: побережье и винные маршруты.',
                'full_description' => 'Тур в разработке, даты и цены уточняются.',
                'duration_days' => 6,
                'duration_nights' => 5,
                'status' => TourStatus::Draft->value,
                'categories' => ['sea'],
            ],
            [
                'slug' => 'ural-industrial-heritage',
                'title' => 'Промышленное наследие Урала',
                'short_description' => 'Черновик экскурсий по заводам и музеям.',
                'full_description' => 'Исторический маршрут по городам Урала.',
                'duration_days' => 5,
                'duration_nights' => 4,
                'status' => TourStatus::Draft->value,
                'categories' => ['culture', 'cities'],
            ],
            [
                'slug' => 'siberia-train-2024',
                'title' => 'Транссиб 2024',
                'short_description' => 'Архивный тур по маршруту Москва — Владивосток.',
                'full_description' => 'Тур снят с публикации, доступен только в архиве админки.',
                'duration_days' => 14,
                'duration_nights' => 13,
                'status' => TourStatus::Archived->value,
                'archived_at' => now()->subMonths(6),
                'categories' => ['cities', 'culture'],
            ],
            [
                'slug' => 'caucasus-wine-2023',
                'title' => 'Винные маршруты Кавказа 2023',
                'short_description' => 'Архивный гастрономический тур.',
                'full_description' => 'Сезон 2023 завершён.',
                'duration_days' => 7,
                'duration_nights' => 6,
                'status' => TourStatus::Archived->value,
                'archived_at' => now()->subYear(),
                'categories' => ['culture'],
            ],
            [
                'slug' => 'murmansk-aurora',
                'title' => 'Охота за северным сиянием',
                'short_description' => 'Мурманск, Териберка и полярная ночь.',
                'full_description' => 'Зимний тур на север с фотосессиями и саамской культурой.',
                'duration_days' => 5,
                'duration_nights' => 4,
                'status' => TourStatus::Published->value,
                'categories' => ['winter', 'adventure'],
                'photos' => [
                    ['path' => 'seed/tours/murmansk-1.jpg', 'alt' => 'Сияние'],
                    ['path' => 'seed/tours/murmansk-2.jpg', 'alt' => 'Териберка'],
                ],
                'route_points' => [
                    ['title' => 'Мурманск', 'lat' => 68.970663, 'lng' => 33.074918],
                    ['title' => 'Териберка', 'lat' => 69.165000, 'lng' => 35.139000],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(60)->toDateString(), 'price' => 98000, 'seats' => 8],
                ],
            ],
            [
                'slug' => 'vladivostok-islands',
                'title' => 'Острова Приморья',
                'short_description' => 'Владивосток, Русский остров и морские прогулки.',
                'full_description' => 'Летний приморский маршрут с акцентом на море.',
                'duration_days' => 6,
                'duration_nights' => 5,
                'status' => TourStatus::Published->value,
                'categories' => ['sea', 'cities'],
                'photos' => [
                    ['path' => 'seed/tours/vladivostok-1.jpg', 'alt' => 'Мост'],
                ],
                'route_points' => [
                    ['title' => 'Владивосток', 'lat' => 43.115542, 'lng' => 131.885494],
                    ['title' => 'Остров Русский', 'lat' => 43.016000, 'lng' => 131.900000],
                ],
                'departures' => [
                    ['start_date' => Carbon::now()->addDays(30)->toDateString(), 'price' => 72000, 'seats' => 14],
                ],
            ],
        ];
    }
}
