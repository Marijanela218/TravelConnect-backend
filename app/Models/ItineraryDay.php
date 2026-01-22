<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryDay extends Model
{
    protected $fillable = [
    'trip_id',
    'day_index',
    'date',
    'title',
];

public function trip()
{
    return $this->belongsTo(Trip::class);
}

public function items()
{
    return $this->hasMany(ItineraryItem::class);
}

}
