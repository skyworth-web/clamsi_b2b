<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
class DemoRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // dd(session()->all());
        $user = Auth::user();
        // dd($user->mobile);
        if (config('constants.ALLOW_MODIFICATION') === 0) {

            if ($user && $user->mobile === '9966338855') {
                return $next($request);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => true, 'error_message' =>  labels('admin_labels.this_operation_is_not_allowed_in_demo_mode', 'This operation is not allowed in demo mode.')]);
            }
            return redirect()->back()->with('error', labels('admin_labels.this_operation_is_not_allowed_in_demo_mode', 'This operation is not allowed in demo mode.'));
        }
        return $next($request);
    }
}
