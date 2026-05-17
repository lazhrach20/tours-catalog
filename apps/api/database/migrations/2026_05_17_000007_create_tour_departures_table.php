<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('RUB');
            $table->unsignedSmallInteger('seats')->nullable();
            $table->timestamps();

            $table->index(['tour_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_departures');
    }
};
