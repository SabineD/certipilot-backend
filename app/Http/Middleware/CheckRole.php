<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $role = $user->role;

        if ($role === User::ROLE_ZAAKVOERDER && in_array(User::ROLE_ADMIN, $roles, true)) {
            return $next($request);
        }

        if ($role === User::ROLE_ADMIN && in_array(User::ROLE_ZAAKVOERDER, $roles, true)) {
            return $next($request);
        }

        if (empty($roles) || in_array($role, $roles, true)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }
}
