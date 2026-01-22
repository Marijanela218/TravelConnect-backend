<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trip;
use App\Models\AiGeneration;

class AiSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $trips = Trip::all();

        // 10 AI logova
        for ($i = 0; $i < 10; $i++) {
            $u = $users->random();
            $trip = $trips->isNotEmpty() ? $trips->random() : null;

            $prompt = [
                'destination' => fake()->city(),
                'days' => rand(2, 7),
                'budget' => rand(150, 1500),
                'interests' => fake()->randomElements(['hrana','muzeji','priroda','nightlife','shopping','plaža'], rand(2,4)),
                'pace' => fake()->randomElement(['lagano','normalno','brzo']),
            ];

            $result = [
                'summary' => fake()->sentence(12),
                'days' => array_map(function ($d) {
                    return [
                        'day' => $d,
                        'items' => [
                            ['type'=>'activity', 'title'=>fake()->sentence(3)],
                            ['type'=>'food', 'title'=>fake()->sentence(3)],
                            ['type'=>'activity', 'title'=>fake()->sentence(3)],
                        ],
                    ];
                }, range(1, $prompt['days'])),
            ];

            AiGeneration::create([
                'user_id' => $u->id,
                'trip_id' => $trip?->id,
                'prompt_json' => $prompt,
                'result_json' => $result,
                'model' => 'mock',
            ]);
        }
    }
}
