<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Auth\Access\AuthorizationException;

class CheckPermissions
{
    public function handle($request, Closure $next, $permissions, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        if ($authGuard->guest()) {

            return $this->unauthorizedResponse('User is not logged in.');
        }

        $user = $authGuard->user();

        // Log the user's role

        $user_role = $user->role;
        $role_name = $user_role->name;

        // Bypass permission check for super_admin
        if ($role_name === 'super_admin') {

            return $next($request);
        }

        $permissions = is_array($permissions) ? $permissions : explode('|', $permissions);

        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {

                return $next($request);
            }
        }


        return $this->unauthorizedResponse('User does not have the required permissions.');
    }

    protected function unauthorizedResponse($message)
    {
        if (request()->expectsJson()) {
            $response = [
                'error' => true,
                'error_message' => $message,
                'data' => [],
            ];
            return response()->json($response);
        }

        throw new AuthorizationException($message);
    }

}
