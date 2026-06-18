<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (is_string($locale) && in_array($locale, ['en', 'ru'], true)) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
            URL::defaults(['locale' => $locale]);
        } elseif (is_string(session('locale'))) {
            $sessionLocale = session('locale');
            app()->setLocale($sessionLocale);
            URL::defaults(['locale' => $sessionLocale]);
        }

        return $next($request);
    }
}
