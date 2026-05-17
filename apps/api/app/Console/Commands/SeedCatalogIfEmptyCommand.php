<?php

namespace App\Console\Commands;

use App\Models\Tour;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class SeedCatalogIfEmptyCommand extends Command
{
    protected $signature = 'catalog:seed-if-empty {--force : Run seeders even when tours already exist}';

    protected $description = 'Seed demo categories, admin user, and tours when the database has no tours';

    public function handle(): int
    {
        if (! $this->option('force') && Tour::query()->exists()) {
            $this->info('Tours already present — skipping catalog seed.');

            return self::SUCCESS;
        }

        $this->info('Seeding catalog (categories, admin, demo tours)...');

        $this->call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
