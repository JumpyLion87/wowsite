<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check cookie for language preference first
        $locale = $request->cookie('locale');
        
        \Log::info('Localization middleware:', [
            'cookie_locale' => $locale,
            'current_locale' => app()->getLocale(),
            'url' => $request->url()
        ]);
        
        // List of supported locales
        $supportedLocales = ['en', 'ru'];
        
        if ($locale && in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
            \Log::info('Locale set from cookie:', ['locale' => $locale]);
        } else {
            // Check browser language preference
            $browserLanguage = $request->getPreferredLanguage(['en', 'ru']);
            if ($browserLanguage) {
                app()->setLocale($browserLanguage);
                \Log::info('Locale set from browser:', ['locale' => $browserLanguage]);
            } else {
                // Fallback to default locale from config
                app()->setLocale(config('app.locale', 'en'));
                \Log::info('Locale set from config:', ['locale' => config('app.locale', 'en')]);
            }
        }

        // Always set cookie to ensure it's updated
        $response = $next($request);
        return $response->withCookie(cookie('locale', app()->getLocale(), 60 * 24 * 30));
    }
}