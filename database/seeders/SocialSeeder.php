<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trip;
use App\Models\TripPost;
use App\Models\Like;
use App\Models\Comment;

class SocialSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $publicTrips = Trip::where('is_public', true)->get();

        if ($users->isEmpty() || $publicTrips->isEmpty()) return;

        // napravi 15 postova
        for ($p = 0; $p < 15; $p++) {
            $trip = $publicTrips->random();
            $user = $users->where('id', $trip->user_id)->first() ?? $users->random();

            $post = TripPost::create([
                'user_id' => $user->id,
                'trip_id' => $trip->id,
                'caption' => rand(0,1) ? fake()->sentence(12) : null,
            ]);

            // random likes (0-8)
            $likeUsers = $users->random(min($users->count(), rand(0, 8)));
            foreach ($likeUsers as $lu) {
                Like::firstOrCreate([
                    'user_id' => $lu->id,
                    'trip_post_id' => $post->id,
                ]);
            }

            // random comments (0-5)
            $commentCount = rand(0, 5);
            for ($c = 0; $c < $commentCount; $c++) {
                $cu = $users->random();
                Comment::create([
                    'user_id' => $cu->id,
                    'trip_post_id' => $post->id,
                    'body' => fake()->sentence(rand(6, 16)),
                ]);
            }
        }
    }
}
