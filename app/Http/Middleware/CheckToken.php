<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
      
        // Check if a valid token is provided
        if (!$request->bearerToken()) {
            return response()->json([
                'error' => true,
                'message' => 'Please provide a valid token',
                'code' => 401,
            ], 401);
        }

        // Check if the user is authenticated
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized',
                'code' => 401,
            ], 401);
        }

        return $next($request);
    }
}
