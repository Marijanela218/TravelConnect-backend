<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryItem extends Model
{
    protected $fillable = [
    'itinerary_day_id',
    'type',
    'title',
    'location',
    'start_time',
    'end_time',
    'notes',
    'cost_estimate',
    'order',
];

    public function day()
{
    return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
}

}
