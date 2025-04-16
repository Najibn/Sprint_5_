<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
/*
        // Ensure user is authenticated via API guard
        if (!Auth::guard('api')->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user has the required role
        $user = Auth::guard('api')->user();
        if (!$user->hasRole($role)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
*/

        if (!$request->user() || $request->user()->role !== $role) {
            return response()->json([
                'message' => 'Unauthorized. Insufficient permissions.'
            ], 403);
        }

        return $next($request);
    }
}
