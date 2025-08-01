<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RememberDeviceSession
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and has remember device enabled
        if (auth()->check() && session('remember_device', false)) {
            // Set session lifetime to 30 days for remember device
            $minutes = 60 * 24 * 30; // 30 days
            
            \Log::info('[RememberDeviceSession] Setting session lifetime for remember device', [
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'lifetime_minutes' => $minutes,
                'remember_device' => session('remember_device', false)
            ]);
            
            // Update session configuration
            config(['session.lifetime' => $minutes]);
            
            // Force session to be saved
            session()->save();
        }
        
        $response = $next($request);
        
        // If remember device is enabled, ensure the session cookie has the correct lifetime
        if (auth()->check() && session('remember_device', false)) {
            $minutes = 60 * 24 * 30; // 30 days
            
            $cookie = cookie(
                config('session.cookie'),
                session()->getId(),
                $minutes,
                config('session.path'),
                config('session.domain'),
                config('session.secure'),
                config('session.http_only'),
                false,
                config('session.same_site')
            );
            
            $response->headers->setCookie($cookie);
            
            \Log::info('[RememberDeviceSession] Session cookie set with remember device lifetime', [
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'cookie_lifetime_minutes' => $minutes
            ]);
        }
        
        return $response;
    }
} 