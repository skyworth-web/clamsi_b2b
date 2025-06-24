<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreNotEmpty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store_count = Store::count();

        // Exclude store setup routes
        $exclude_routes = ['admin.stores.index'];
        $current_route = $request->route() ? $request->route()->getName() : null;

        if ($store_count === 0 && !in_array($current_route, $exclude_routes)) {
            return redirect()->route('admin.stores.index')->with('error', 'Please set up your store first.');
        }

        return $next($request);
    }
}
