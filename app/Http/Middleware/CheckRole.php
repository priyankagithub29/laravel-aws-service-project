<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if the user is authenticated via JWT
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthenticated. Please log in first.'
            ], 401);
        }

        // 2. Get the authenticated user's role
        $userRole = Auth::user()->role;

        // 3. Check if the user's role is allowed for this route
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'error' => 'Unauthorized. You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}