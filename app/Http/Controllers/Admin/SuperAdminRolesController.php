<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminRolesController extends Controller
{
    public function sync(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required','array','min:1'],
            'roles.*' => ['string'],
        ]);

        $roleIds = Role::whereIn('name', $data['roles'])->pluck('id')->all();
        $user->roles()->sync($roleIds);

        return response()->json([
            'message' => 'Roles updated.',
            'user' => $user->load('roles:id,name'),
        ]);
    }
}
