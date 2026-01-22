<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Trip extends Model
{
    protected $fillable = [
    'user_id',
    'title',
    'destination',
    'start_date',
    'end_date',
    'budget',
    'travel_style',
    'pace',
    'is_public',
];
    public function user()
{
    return $this->belongsTo(User::class);
}

public function days()
{
    return $this->hasMany(ItineraryDay::class);
}
public function posts()
{
    return $this->hasMany(\App\Models\TripPost::class);
}
public function aiGenerations()
{
    return $this->hasMany(\App\Models\AiGeneration::class);
}

}
