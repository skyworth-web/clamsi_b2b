<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultStore
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sqlDumpPath = base_path('eshop_plus.sql');
        $installViewPath = resource_path('views/install.blade.php');
        // Check if the installation has been completed
        if (!file_exists($sqlDumpPath) && !file_exists($installViewPath)) {
            $defaultStore = Store::where('is_default_store', 1)
                ->where('status', 1)
                ->first();

            $default_store_id = $defaultStore ? $defaultStore->id : '';
            $default_store_name = $defaultStore ? $defaultStore->name : '';
            $default_store_image = $defaultStore ? $defaultStore->image : '';
            $default_store_slug = $defaultStore ? $defaultStore->slug : '';

            session([
                'store_id' => session('store_id', $default_store_id),
                'store_name' => session('store_name', $default_store_name),
                'store_image' => session('store_image', $default_store_image),
                'store_slug' => session('store_slug', $default_store_slug),
                'default_store_slug' => session('default_store_slug', $default_store_slug),
            ]);
            if (!$request->session()->has('show_store_popup')) {
                $request->session()->put('show_store_popup', true);
            }
            if (isset($request->query()['store']) && ($request->query()['store'] != null)) {
                $store_slug = $request->query()['store'];
                $store = fetchDetails('stores', ['slug' => $store_slug], "*");
                if (count($store) <= 0) {
                    return redirect(customUrl(url()->current()));
                }
                if (isset($store[0]) && $store[0]->id != session('store_id')) {
                    session()->forget(['store_id', 'store_name', 'store_image', 'store_slug']);
                    session()->put('store_id', $store[0]->id);
                    session()->put('store_name', $store[0]->name);
                    session()->put('store_image', $store[0]->image);
                    session()->put('store_slug', $store[0]->slug);
                }
            }
        }
        return $next($request);
    }
}
