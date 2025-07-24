<?php

namespace App\Http\Middleware;

use Closure;

class CustomSessionCookie
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $remember = session('remember_device', false);
        $minutes = $remember ? (60 * 24 * 30) : null; // 30 days or session-only
        
        \Log::info('[CustomSessionCookie] Middleware', [
            'remember_device' => $remember,
            'session_id' => session()->getId(),
            'cookie_lifetime_minutes' => $minutes,
            'user_id' => auth()->id(),
        ]);

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

        if (auth()->check()) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
} 