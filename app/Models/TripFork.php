<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripFork extends Model
{
    protected $fillable = [
    'original_trip_id',
    'new_trip_id',
    'forked_by_user_id',
];

}
