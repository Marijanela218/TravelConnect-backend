<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Niste prijavljeni.'], 401);
        }

        $user->loadMissing('roles');

        $hasRole = $user->roles->pluck('name')->intersect($roles)->isNotEmpty();

        if (!$hasRole) {
            return response()->json(['message' => 'Nemaš pravo pristupa.'], 403);
        }

        return $next($request);
    }
}