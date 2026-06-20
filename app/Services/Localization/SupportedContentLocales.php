<?php

namespace App\Services\Localization;

use Illuminate\Support\Collection;

class SupportedContentLocales
{
    /**
     * @return list<string>
     */
    public function locales(): array
    {
        return collect(config('localization.supported_locales', []))
            ->filter(fn (mixed $locale): bool => is_string($locale) && trim($locale) !== '')
            ->map(fn (string $locale): string => strtolower(str_replace('_', '-', trim($locale))))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function preferred(?string $locale = null, ?string $fallbackLocale = null): array
    {
        return collect([
            $locale ?: app()->getLocale(),
            $fallbackLocale ?: config('localization.fallback_locale', config('app.fallback_locale', 'en')),
            ...$this->locales(),
        ])
            ->filter(fn (mixed $candidate): bool => is_string($candidate) && trim($candidate) !== '')
            ->map(fn (string $candidate): string => strtolower(str_replace('_', '-', trim($candidate))))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, object>  $translations
     * @param  list<string>  $requiredFields
     */
    public function hasAllTranslations(Collection $translations, array $requiredFields): bool
    {
        return $this->missingLocales($translations, $requiredFields) === [];
    }

    /**
     * @param  Collection<int, object>  $translations
     * @param  list<string>  $requiredFields
     * @return list<string>
     */
    public function missingLocales(Collection $translations, array $requiredFields): array
    {
        return collect($this->locales())
            ->reject(fn (string $locale): bool => $this->hasTranslation($translations, $locale, $requiredFields))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $locales
     */
    public function names(array $locales, ?string $displayLocale = null): string
    {
        return collect($locales)
            ->map(fn (string $locale): string => $this->name($locale, $displayLocale))
            ->implode(', ');
    }

    public function name(string $locale, ?string $displayLocale = null): string
    {
        $key = 'navigation.languages.'.$locale;
        $label = __($key, [], $displayLocale);

        if ($label !== $key) {
            return $label;
        }

        $configuredName = config('localization.locale_names.'.$locale);

        return is_string($configuredName) && $configuredName !== ''
            ? $configuredName
            : strtoupper($locale);
    }

    /**
     * @param  Collection<int, object>  $translations
     * @param  list<string>  $requiredFields
     */
    private function hasTranslation(Collection $translations, string $locale, array $requiredFields): bool
    {
        $translation = $translations->firstWhere('locale', $locale);

        if ($translation === null) {
            return false;
        }

        foreach ($requiredFields as $field) {
            if (blank(data_get($translation, $field))) {
                return false;
            }
        }

        return true;
    }
}
