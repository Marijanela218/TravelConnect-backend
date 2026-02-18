<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'roles' => Role::select('id','name')->get(),
            'can_assign_roles' => true,
        ]);
    }
}
