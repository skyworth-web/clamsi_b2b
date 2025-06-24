<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogoutMiddleware
{
    // public function handle($request, Closure $next)
    // {
    //     $response = $next($request);

    //     return $response->withHeaders([
    //         'Cache-Control' => 'no-cache, no-store, must-revalidate',
    //         'Pragma' => 'no-cache',
    //         'Expires' => '0',
    //     ]);
    // }
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }
        return $response->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
