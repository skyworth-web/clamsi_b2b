<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request)
    {
        
        if ($request->expectsJson()) {
            return route('admin.login'); // Don't redirect for JSON requests
        }
    
        if ($request->routeIs('admin.*') || !auth()->check()) {
            return route('admin.login'); // Redirect to the admin login route
        }
    
    }

    protected function unauthenticated($request, array $guards)
    {
        if ($request->is('api/*')) {
            if (!$request->bearerToken()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please provide a valid token',
                    'code' => 401,
                ], 401);
            }
            // For API requests
            abort(response()->json(['error' => true,'message' => 'Unauthenticated.','code' => 401],));
        } 
    }
    
}
