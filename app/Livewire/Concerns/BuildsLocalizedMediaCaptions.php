<?php

namespace App\Livewire\Concerns;

use App\Services\Localization\SupportedContentLocales;

trait BuildsLocalizedMediaCaptions
{
    /**
     * @return array<string, string>
     */
    private function localizedCaptions(string $translationKey): array
    {
        return collect(app(SupportedContentLocales::class)->locales())
            ->mapWithKeys(function (string $locale) use ($translationKey): array {
                $caption = __($translationKey, [], $locale);

                return $caption === $translationKey ? [] : [$locale => $caption];
            })
            ->all();
    }
}
