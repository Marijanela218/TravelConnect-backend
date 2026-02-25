<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class SuperAdminRolesController extends Controller
{
    public function sync(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['in:admin,super_admin,user'],
        ]);

        $roles = Role::whereIn('name', $data['roles'])->pluck('id');

        $user->roles()->sync($roles);

        return response()->json([
            'message' => 'Roles updated.',
            'user' => $user->load('roles'),
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}