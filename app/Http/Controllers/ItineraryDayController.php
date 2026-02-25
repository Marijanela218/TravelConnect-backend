<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\ItineraryDay;
use Illuminate\Http\Request;

class ItineraryDayController extends Controller
{
    
    public function store(Request $request, Trip $trip)
    {
       
        if ($trip->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo mijenjati ovo putovanje.');
        }

        $data = $request->validate([
            'day_index' => ['required','integer','min:1'],
            'date' => ['nullable','date'],
            'title' => ['nullable','string','max:255'],
        ]);

        $day = ItineraryDay::create([
            ...$data,
            'trip_id' => $trip->id,
        ]);

        return response()->json(['day' => $day], 201);
    }

    
    public function update(Request $request, ItineraryDay $day)
    {
        $trip = $day->trip;

        if ($trip->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo mijenjati ovo putovanje.');
        }

        $data = $request->validate([
            'day_index' => ['sometimes','required','integer','min:1'],
            'date' => ['nullable','date'],
            'title' => ['nullable','string','max:255'],
        ]);

        $day->update($data);

        return response()->json(['day' => $day]);
    }

    
    public function destroy(Request $request, ItineraryDay $day)
    {
        $trip = $day->trip;

        if ($trip->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo mijenjati ovo putovanje.');
        }

        $day->delete();

        return response()->json(['message' => 'Dan obrisan.']);
    }
}
