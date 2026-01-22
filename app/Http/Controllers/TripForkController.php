<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripFork;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripForkController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        if ($trip->user_id === $request->user()->id) {
            abort(422, 'Ne možeš forkati svoje putovanje.');
        }

        if (!$trip->is_public) {
            abort(403, 'Ovo putovanje nije public.');
        }

        $existing = TripFork::where('original_trip_id', $trip->id)
            ->where('forked_by_user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Već si forkala ovo putovanje.',
                'new_trip_id' => $existing->new_trip_id,
            ]);
        }

        $newTrip = DB::transaction(function () use ($request, $trip) {

            $copy = Trip::create([
                'user_id' => $request->user()->id,
                'title' => $trip->title . ' (kopija)',
                'destination' => $trip->destination,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'budget' => $trip->budget,
                'travel_style' => $trip->travel_style,
                'pace' => $trip->pace,
                'is_public' => false, 
            ]);

            $trip->load('days.items');

            foreach ($trip->days as $day) {
                $newDay = ItineraryDay::create([
                    'trip_id' => $copy->id,
                    'day_index' => $day->day_index,
                    'date' => $day->date,
                    'title' => $day->title,
                ]);

                foreach ($day->items as $item) {
                    ItineraryItem::create([
                        'itinerary_day_id' => $newDay->id,
                        'type' => $item->type,
                        'title' => $item->title,
                        'location' => $item->location,
                        'start_time' => $item->start_time,
                        'end_time' => $item->end_time,
                        'notes' => $item->notes,
                        'cost_estimate' => $item->cost_estimate,
                        'order' => $item->order,
                    ]);
                }
            }

            TripFork::create([
                'original_trip_id' => $trip->id,
                'new_trip_id' => $copy->id,
                'forked_by_user_id' => $request->user()->id,
            ]);

            return $copy;
        });

        return response()->json([
            'message' => 'Putovanje forkano.',
            'trip' => $newTrip->load('days.items'),
        ], 201);
    }
}
