<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // dd($request->headers->all());
        // Fetch language_id from headers
        $language_id = $request->header('Language-Id', '');

        // Fetch the language code from the database or default to 'en' if not found
        $language_code = Language::where('id', $language_id)->value('code') ?? 'en';

        // Store the language ID and code in the request object for global access
        $request->attributes->set('language_id', $language_id);
        $request->attributes->set('language_code', $language_code);

        return $next($request);
    }
}
