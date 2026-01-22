<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('itinerary_day_id')->constrained('itinerary_days')->cascadeOnDelete();

            $table->enum('type', ['activity', 'food', 'transport', 'hotel'])->default('activity');

            $table->string('title');
            $table->string('location')->nullable();

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->text('notes')->nullable();
            $table->decimal('cost_estimate', 10, 2)->nullable();

            $table->unsignedInteger('order')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_items');
    }
};
