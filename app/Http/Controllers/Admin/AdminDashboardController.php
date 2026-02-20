<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trip;
use App\Models\TripPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'users_count' => User::count(),
            'trips_count' => Trip::count(),
            'posts_count' => TripPost::count(),
        ]);
    }
    
  
}
