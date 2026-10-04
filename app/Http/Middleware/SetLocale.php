<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the visitor's preferred locale to the current request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolveLocale($request));

        return $next($request);
    }

    /**
     * Resolve the locale from the user profile, the guest cookie, or the default.
     */
    private function resolveLocale(Request $request): string
    {
        $supported = config('app.supported_locales');
        $locale = $request->user()->locale ?? $request->cookie('locale');

        return is_string($locale) && in_array($locale, $supported, true)
            ? $locale
            : config('app.locale');
    }
}
