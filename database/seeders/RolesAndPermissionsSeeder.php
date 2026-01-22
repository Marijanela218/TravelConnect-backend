<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
            'user',
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        $permissions = [
            'trips.create',
            'trips.update',
            'trips.delete',
            'posts.moderate',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Poveži dozvole s rolama
        $admin = Role::where('name', 'admin')->first();
        $super = Role::where('name', 'super_admin')->first();
        $user  = Role::where('name', 'user')->first();

        $admin->permissions()->sync(Permission::pluck('id'));
        $super->permissions()->sync(Permission::pluck('id'));

        $user->permissions()->sync(
            Permission::whereIn('name', ['trips.create','trips.update'])->pluck('id')
        );
    }
}
