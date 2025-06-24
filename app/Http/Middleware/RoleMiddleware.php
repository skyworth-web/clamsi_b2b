<?php

namespace App\Http\Middleware;


use Closure;
use App\Models\User;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$roles)
    {
        
        if (!Auth::check()) {
            if ($request->routeIs('admin.*')) {
                return redirect('admin/login'); // Redirect to the admin login route
            } else if ($request->routeIs('seller.*')) {
                return redirect('seller/login'); // Redirect to the seller login route
            } else {
                return redirect('admin/login');
            }
        }
        $user = User::with('role')->find(Auth::user()->id);
        $allowedRoles = is_array($roles) ? $roles : [$roles];
        if (in_array($user->role->name, $allowedRoles)) {
            return $next($request);
        }

        throw UnauthorizedException::forRoles($roles);
    }
}
