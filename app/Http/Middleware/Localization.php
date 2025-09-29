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
        
        // List of supported locales
        $supportedLocales = ['en', 'ru'];
        
        if ($locale && in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
        } else {
            // Check browser language preference
            $browserLanguage = $request->getPreferredLanguage(['en', 'ru']);
            if ($browserLanguage) {
                app()->setLocale($browserLanguage);
            } else {
                // Fallback to default locale from config
                app()->setLocale(config('app.locale', 'en'));
            }
            
            // Set cookie for language if not already set
            $response = $next($request);
            return $response->withCookie(cookie('locale', app()->getLocale(), 60 * 24 * 30));
        }

        return $next($request);
    }
}