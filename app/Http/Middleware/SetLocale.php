<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the session locale (default: Egyptian Arabic).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'ar');

        // Guard against a tampered/invalid session value.
        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        // Localize date/number formatting for the current locale.
        Carbon::setLocale($locale);
        Number::useLocale($locale);

        return $next($request);
    }
}
