<?php

namespace App\Services\Localization;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final readonly class LocalizedModelContentResolver
{
    public function __construct(private string $fallbackLocale) {}

    /**
     * @param  Collection<int, covariant Model>  $translations
     */
    public function resolve(
        Collection $translations,
        string $requestedLocale,
        ?string $sourceLocale = null,
    ): ?Model {
        $locales = array_values(array_unique(array_filter([
            $requestedLocale,
            $this->fallbackLocale,
            $sourceLocale,
        ])));

        foreach ($locales as $locale) {
            $translation = $translations->firstWhere('locale', $locale);

            if ($translation instanceof Model) {
                return $translation;
            }
        }

        return null;
    }
}
