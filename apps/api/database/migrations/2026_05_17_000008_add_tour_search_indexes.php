<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE INDEX tours_search_tsvector_idx ON tours
            USING GIN (to_tsvector('russian', coalesce(title, '') || ' ' || coalesce(short_description, '')))
        SQL);

        DB::statement(<<<'SQL'
            CREATE INDEX tours_embedding_hnsw_idx ON tours
            USING hnsw (embedding vector_cosine_ops)
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS tours_embedding_hnsw_idx');
        DB::statement('DROP INDEX IF EXISTS tours_search_tsvector_idx');
    }
};
