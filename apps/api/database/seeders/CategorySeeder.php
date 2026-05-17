<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Горы', 'slug' => 'mountains', 'icon' => 'mountain', 'color' => '#2563eb'],
            ['name' => 'Море и побережье', 'slug' => 'sea', 'icon' => 'waves', 'color' => '#0891b2'],
            ['name' => 'Города', 'slug' => 'cities', 'icon' => 'building', 'color' => '#7c3aed'],
            ['name' => 'Экотуризм', 'slug' => 'eco', 'icon' => 'leaf', 'color' => '#16a34a'],
            ['name' => 'Культура', 'slug' => 'culture', 'icon' => 'landmark', 'color' => '#ca8a04'],
            ['name' => 'Приключения', 'slug' => 'adventure', 'icon' => 'compass', 'color' => '#ea580c'],
            ['name' => 'Зимний отдых', 'slug' => 'winter', 'icon' => 'snowflake', 'color' => '#0284c7'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
