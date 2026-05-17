<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('short_description');
            $table->longText('full_description');
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('duration_nights')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        DB::statement('ALTER TABLE tours ADD COLUMN embedding vector(384)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
