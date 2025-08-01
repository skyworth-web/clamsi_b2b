<?php

namespace App\Http\Middleware;

use Closure;

class CustomSessionCookie
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Check if session is available and user is authenticated
        if (auth()->check() && session()->isStarted()) {
            $remember = session('remember_device', false);
            
            // If remember device is enabled, set session lifetime to 30 days
            if ($remember) {
                $minutes = 60 * 24 * 30; // 30 days
                
                \Log::info('[CustomSessionCookie] Setting remember device session', [
                    'remember_device' => $remember,
                    'session_id' => session()->getId(),
                    'cookie_lifetime_minutes' => $minutes,
                    'user_id' => auth()->id(),
                ]);

                // Set the session cookie with 30-day lifetime
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
                
                // Force session to be saved
                session()->save();
                
                \Log::info('[CustomSessionCookie] Session saved for remember device', [
                    'session_id' => session()->getId(),
                    'user_id' => auth()->id(),
                    'remember_device' => $remember,
                    'cookie_lifetime_minutes' => $minutes
                ]);
            } else {
                \Log::info('[CustomSessionCookie] Normal session (no remember device)', [
                    'remember_device' => $remember,
                    'session_id' => session()->getId(),
                    'user_id' => auth()->id(),
                ]);
            }
        } else {
            \Log::info('[CustomSessionCookie] Middleware - User not authenticated or session not started', [
                'is_authenticated' => auth()->check(),
                'session_started' => session()->isStarted(),
                'user_id' => auth()->id()
            ]);
        }

        return $response;
    }
} 