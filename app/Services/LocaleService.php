<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\App;

class LocaleService
{
    /**
     * @return list<string>
     */
    public function supported(): array
    {
        return array_values(config('localization.supported_locales', ['en', 'ru']));
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supported(), true);
    }

    public function apply(string $locale, ?User $user = null): string
    {
        $locale = $this->isSupported($locale) ? $locale : (string) config('app.fallback_locale', 'en');

        App::setLocale($locale);
        session(['locale' => $locale]);

        if ($user) {
            $user->forceFill(['preferred_locale' => $locale])->save();
            $user->setting()->updateOrCreate(['user_id' => $user->id], ['locale' => $locale]);
        }

        return $locale;
    }
}
