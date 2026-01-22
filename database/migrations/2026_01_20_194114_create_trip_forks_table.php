<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trip_forks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('original_trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('new_trip_id')->constrained('trips')->cascadeOnDelete();

            $table->foreignId('forked_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
            
            $table->unique(['original_trip_id', 'forked_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_forks');
    }
};
