<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('itinerary_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();

            $table->unsignedInteger('day_index');
            $table->date('date')->nullable();
            $table->string('title')->nullable();

            $table->timestamps();

            $table->unique(['trip_id', 'day_index']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_days');
    }
};
