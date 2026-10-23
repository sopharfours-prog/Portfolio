<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chooses the interface language.
 *  - site:  visitor's choice (session) → default language from Settings
 *  - admin: admin's choice (session) → user's saved locale → app default
 */
class SetLocale
{
    public function handle(Request $request, Closure $next, string $area = 'site'): Response
    {
        $supported = array_keys(locales());

        $locale = $area === 'admin'
            ? $request->session()->get('admin_locale', $request->user()?->locale)
            : $request->session()->get('locale', Setting::get('language.default'));

        app()->setLocale(in_array($locale, $supported, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
