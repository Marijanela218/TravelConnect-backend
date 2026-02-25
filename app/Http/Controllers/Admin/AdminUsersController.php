<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminUsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();

        $users = User::query()
            ->select('id','name','email','username')
            ->with('roles:id,name')
            ->orderByDesc('id')
            ->get();

        return response()->json(['users' => $users]);
    }
}
