<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminRolesController extends Controller
{
    // 🔁 Sync roles
    public function sync(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required','array','min:1'],
            'roles.*' => ['string'],
        ]);

        $roleIds = Role::whereIn('name', $data['roles'])
            ->pluck('id')
            ->all();

        $user->roles()->sync($roleIds);

        return response()->json([
            'message' => 'Roles updated.',
            'user' => $user->load('roles:id,name'),
        ]);
    }

    // 🗑 Delete user
    public function destroy(Request $request, User $user)
    {
        // ❗ Ne dozvoli da super admin obriše sam sebe
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 422);
        }

        $user->tokens()->delete(); // izbriši API tokene
        $user->roles()->detach();  // očisti pivot
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}
