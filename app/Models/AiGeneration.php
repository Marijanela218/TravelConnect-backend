<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'trip_id',
        'prompt_json',
        'result_json',
        'model',
    ];

    protected $casts = [
        'prompt_json' => 'array',
        'result_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
