<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated user
        $user = $request->user();

        // Check if user exists and has admin role
        if (!$user || ($user->level !== 'admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke fitur ini. Hanya admin yang diizinkan.',
            ], 401);
        }

        return $next($request);
    }
}
