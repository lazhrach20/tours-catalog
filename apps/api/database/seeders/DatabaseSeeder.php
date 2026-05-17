<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AdminUserSeeder::class,
            TourSeeder::class,
        ]);

        try {
            Artisan::call('tours:reindex-embeddings');
            $this->command?->info(trim(Artisan::output()));
        } catch (\Throwable) {
            $this->command?->warn(
                'Embeddings reindex skipped (start embeddings service or set MOCK_EMBEDDINGS=1, then run make reindex-embeddings).',
            );
        }
    }
}
