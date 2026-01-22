<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $superRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $userRole  = Role::where('name', 'user')->first();

        // Super admin
        $super = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'user_type' => 'super_admin',
                'password' => Hash::make('password123'),
            ]
        );
        if ($superRole) $super->roles()->syncWithoutDetaching([$superRole->id]);

        // Admin
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'user_type' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );
        if ($adminRole) $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // Obični korisnici (npr 10)
        User::factory()->count(10)->create([
            'user_type' => 'user',
        ])->each(function ($u) use ($userRole) {
            if ($userRole) $u->roles()->syncWithoutDetaching([$userRole->id]);
        });
    }
}
