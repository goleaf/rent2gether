<?php

namespace App\Livewire\Concerns;

use App\Services\Localization\SupportedContentLocales;
use Illuminate\Support\Collection;

trait ManagesLocalizedFormTranslations
{
    /** @var array<string, array<string, string>> */
    public array $translations = [];

    /**
     * @return list<array{code:string,name:string}>
     */
    public function contentLocales(): array
    {
        $locales = app(SupportedContentLocales::class);

        return collect($locales->locales())
            ->map(fn (string $locale): array => [
                'code' => $locale,
                'name' => $locales->name($locale),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $fields
     */
    private function fillBlankTranslations(array $fields): void
    {
        foreach (app(SupportedContentLocales::class)->locales() as $locale) {
            foreach ($fields as $field) {
                $this->translations[$locale][$field] ??= '';
            }
        }
    }

    /**
     * @param  Collection<int, object>  $modelTranslations
     * @param  list<string>  $fields
     */
    private function loadLocalizedTranslations(Collection $modelTranslations, array $fields): void
    {
        $this->fillBlankTranslations($fields);

        foreach ($modelTranslations as $translation) {
            $locale = (string) data_get($translation, 'locale');

            if (! in_array($locale, app(SupportedContentLocales::class)->locales(), true)) {
                continue;
            }

            foreach ($fields as $field) {
                $this->translations[$locale][$field] = (string) (data_get($translation, $field) ?? '');
            }
        }
    }

    /**
     * @param  array<string, list<mixed>>  $fieldRules
     * @return array<string, mixed>
     */
    private function localizedTranslationRules(array $fieldRules): array
    {
        $rules = ['translations' => ['array']];

        foreach (app(SupportedContentLocales::class)->locales() as $locale) {
            foreach ($fieldRules as $field => $fieldRule) {
                $rules['translations.'.$locale.'.'.$field] = $fieldRule;
            }
        }

        return $rules;
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, string>
     */
    private function localizedValidationAttributes(string $translationPrefix, array $fields): array
    {
        $attributes = [];
        $locales = app(SupportedContentLocales::class);

        foreach ($locales->locales() as $locale) {
            foreach ($fields as $field) {
                $attributes['translations.'.$locale.'.'.$field] = __($translationPrefix.'.'.$field, [
                    'language' => $locales->name($locale),
                ]);
            }
        }

        return $attributes;
    }

    private function translationValue(string $locale, string $field): string
    {
        return (string) data_get($this->translations, $locale.'.'.$field, '');
    }

    private function firstTranslationValue(string $field): string
    {
        $locales = app(SupportedContentLocales::class);

        foreach ($locales->preferred() as $locale) {
            $value = trim($this->translationValue($locale, $field));

            if ($value !== '') {
                return $value;
            }
        }

        foreach ($locales->locales() as $locale) {
            $value = trim($this->translationValue($locale, $field));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function hasEveryLocaleValue(string $field): bool
    {
        foreach (app(SupportedContentLocales::class)->locales() as $locale) {
            if (trim($this->translationValue($locale, $field)) === '') {
                return false;
            }
        }

        return true;
    }
}
