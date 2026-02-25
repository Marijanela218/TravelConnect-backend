<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;


    
    protected $fillable = [
        'name',
        'email',
        'username',
        'user_type',
        'password',
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

        protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles()
{
    return $this->belongsToMany(\App\Models\Role::class);
}

public function hasRole(string $roleName): bool
{
    return $this->roles()->where('name', $roleName)->exists();
}

public function permissions()
{
    return \App\Models\Permission::query()
        ->whereHas('roles', function ($q) {
            $q->whereIn('roles.id', $this->roles()->pluck('roles.id'));
        });
}

public function hasPermission(string $permissionName): bool
{
    return $this->permissions()->where('name', $permissionName)->exists();
}
public function posts()
{
    return $this->hasMany(\App\Models\TripPost::class);
}

public function likes()
{
    return $this->hasMany(\App\Models\Like::class);
}
public function aiGenerations()
{
    return $this->hasMany(\App\Models\AiGeneration::class);
}

}
