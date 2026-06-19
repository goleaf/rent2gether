<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('localization.supported_locales');
        $routeLocale = $request->route('locale');
        $sessionLocale = $request->session()->get('locale');
        $previousLocale = is_string($sessionLocale) ? $sessionLocale : null;

        $locale = $this->supported($routeLocale, $supportedLocales)
            ?? $this->supported($sessionLocale, $supportedLocales)
            ?? $this->userLocale($request, $supportedLocales)
            ?? config('app.locale');

        App::setLocale($locale);
        $request->session()->put('locale', $locale);
        URL::defaults(['locale' => $locale]);

        $user = $request->user();

        if ($user && ($previousLocale !== $locale || $request->session()->get('locale_user_id') !== $user->id)) {
            $user->setting()->updateOrCreate([], ['locale' => $locale]);
            $request->session()->put('locale_user_id', $user->id);
        }

        return $next($request);
    }

    /** @param  list<string>  $supportedLocales */
    private function supported(mixed $locale, array $supportedLocales): ?string
    {
        return is_string($locale) && in_array($locale, $supportedLocales, true)
            ? $locale
            : null;
    }

    /** @param  list<string>  $supportedLocales */
    private function userLocale(Request $request, array $supportedLocales): ?string
    {
        return $this->supported(
            $request->user()?->setting()->value('locale'),
            $supportedLocales,
        );
    }
}
