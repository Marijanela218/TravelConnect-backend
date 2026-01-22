<?php

namespace App\Http\Controllers;

use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use Illuminate\Http\Request;

class ItineraryItemController extends Controller
{
    // POST /api/days/{day}/items
    public function store(Request $request, ItineraryDay $day)
    {
        $trip = $day->trip;

        if ($trip->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo mijenjati ovo putovanje.');
        }

        $data = $request->validate([
            'type' => ['nullable','in:activity,food,transport,hotel'],
            'title' => ['required','string','max:255'],
            'location' => ['nullable','string','max:255'],
            'start_time' => ['nullable','date_format:H:i'],
            'end_time' => ['nullable','date_format:H:i'],
            'notes' => ['nullable','string'],
            'cost_estimate' => ['nullable','numeric','min:0'],
            'order' => ['nullable','integer','min:1'],
        ]);

        $item = ItineraryItem::create([
            ...$data,
            'itinerary_day_id' => $day->id,
            'type' => $data['type'] ?? 'activity',
            'order' => $data['order'] ?? 1,
        ]);

        return response()->json(['item' => $item], 201);
    }

    // PUT /api/items/{item}
    public function update(Request $request, ItineraryItem $item)
    {
        $day = $item->day;
        $trip = $day->trip;

        if ($trip->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo mijenjati ovo putovanje.');
        }

        $data = $request->validate([
            'type' => ['nullable','in:activity,food,transport,hotel'],
            'title' => ['sometimes','required','string','max:255'],
            'location' => ['nullable','string','max:255'],
            'start_time' => ['nullable','date_format:H:i'],
            'end_time' => ['nullable','date_format:H:i'],
            'notes' => ['nullable','string'],
            'cost_estimate' => ['nullable','numeric','min:0'],
            'order' => ['nullable','integer','min:1'],
        ]);

        $item->update($data);

        return response()->json(['item' => $item]);
    }

    // DELETE /api/items/{item}
    public function destroy(Request $request, ItineraryItem $item)
    {
        $day = $item->day;
        $trip = $day->trip;

        if ($trip->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo mijenjati ovo putovanje.');
        }

        $item->delete();

        return response()->json(['message' => 'Stavka obrisana.']);
    }
}
