<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trip;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;

class TripsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $destinations = ['Rim', 'Pariz', 'Beč', 'Istanbul', 'Barcelona', 'Prag', 'London'];

        foreach ($users as $u) {
            // svaki user 1-2 putovanja
            $tripCount = rand(1, 2);

            for ($t = 0; $t < $tripCount; $t++) {
                $dest = $destinations[array_rand($destinations)];
                $daysCount = rand(3, 5);

                $trip = Trip::create([
                    'user_id' => $u->id,
                    'title' => $dest . ' - ' . $daysCount . ' dana',
                    'destination' => $dest,
                    'start_date' => now()->addDays(rand(1, 30))->toDateString(),
                    'end_date' => now()->addDays(rand(31, 60))->toDateString(),
                    'budget' => rand(200, 1200),
                    'travel_style' => ['budget','mid','luxury'][array_rand(['budget','mid','luxury'])],
                    'pace' => ['lagano','normalno','brzo'][array_rand(['lagano','normalno','brzo'])],
                    'is_public' => (bool)rand(0, 1),
                ]);

                // days + items
                for ($d = 1; $d <= $daysCount; $d++) {
                    $day = ItineraryDay::create([
                        'trip_id' => $trip->id,
                        'day_index' => $d,
                        'date' => null,
                        'title' => 'Dan ' . $d,
                    ]);

                    $itemsCount = rand(3, 6);

                    for ($i = 1; $i <= $itemsCount; $i++) {
                        ItineraryItem::create([
                            'itinerary_day_id' => $day->id,
                            'type' => ['activity','food','transport','hotel'][array_rand(['activity','food','transport','hotel'])],
                            'title' => fake()->sentence(3),
                            'location' => $dest,
                            'start_time' => null,
                            'end_time' => null,
                            'notes' => rand(0,1) ? fake()->sentence(10) : null,
                            'cost_estimate' => rand(0,1) ? rand(5, 80) : null,
                            'order' => $i,
                        ]);
                    }
                }
            }
        }
    }
}
