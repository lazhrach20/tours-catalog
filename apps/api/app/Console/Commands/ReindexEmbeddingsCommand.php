<?php

namespace App\Console\Commands;

use App\Enums\TourStatus;
use App\Models\Tour;
use App\Services\Embeddings\TourEmbeddingService;
use Illuminate\Console\Command;

class ReindexEmbeddingsCommand extends Command
{
    protected $signature = 'tours:reindex-embeddings {--chunk=50 : Number of tours per batch}';

    protected $description = 'Recompute embeddings for all published tours';

    public function handle(TourEmbeddingService $tourEmbeddingService): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $processed = 0;
        $failed = 0;

        Tour::query()
            ->where('status', TourStatus::Published)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($tours) use ($tourEmbeddingService, &$processed, &$failed): void {
                foreach ($tours as $tour) {
                    try {
                        $tourEmbeddingService->index($tour);
                        $processed++;
                        $this->line("Indexed tour #{$tour->id} ({$tour->slug})");
                    } catch (\Throwable $exception) {
                        $failed++;
                        $this->error("Failed tour #{$tour->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->info("Done. Indexed: {$processed}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
