<?php

namespace App\Console\Commands;

use App\Services\Catalog\AmenityRuleCatalog;
use BackedEnum;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Throwable;

#[Signature('translations:missing')]
#[Description('Report translation files and keys missing from a supported locale')]
class ReportMissingTranslations extends Command
{
    public function handle(): int
    {
        $locales = config('localization.supported_locales');
        $catalogues = [];
        $expectedKeys = [];
        $problems = [];
        $seenProblems = [];

        foreach ($locales as $locale) {
            $catalogue = $this->catalogue($locale);
            $catalogues[$locale] = $catalogue;

            foreach ($catalogue as $key => $value) {
                $expectedKeys[$key] = true;
            }
        }

        ksort($expectedKeys);

        foreach ($catalogues as $locale => $catalogue) {
            foreach ($expectedKeys as $key => $exists) {
                if (! array_key_exists($key, $catalogue)) {
                    $this->addProblem($problems, $seenProblems, 'missing_key', $locale, $key);
                }
            }
        }

        foreach ($this->referencedKeys() as $key) {
            foreach ($catalogues as $locale => $catalogue) {
                if (! array_key_exists($key, $catalogue)) {
                    $this->addProblem($problems, $seenProblems, 'referenced_key', $locale, $key);
                }
            }
        }

        foreach ($this->enumLabelProblems($locales) as $problem) {
            $this->addProblem($problems, $seenProblems, ...$problem);
        }

        foreach ($this->catalogueSeedProblems() as $problem) {
            $this->addProblem($problems, $seenProblems, ...$problem);
        }

        foreach ($this->validationAttributeProblems($catalogues) as $problem) {
            $this->addProblem($problems, $seenProblems, ...$problem);
        }

        foreach ($this->hardCodedBladeTextProblems() as $problem) {
            $this->addProblem($problems, $seenProblems, ...$problem);
        }

        if ($problems !== []) {
            $this->components->error(__('app.translation_report.missing'));
            $this->table(
                [__('app.translation_report.type'), __('app.translation_report.locale'), __('app.translation_report.key')],
                $problems,
            );

            return self::FAILURE;
        }

        $this->components->info(__('app.translation_report.complete', ['count' => count($expectedKeys)]));

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function catalogue(string $locale): array
    {
        $catalogue = [];

        foreach (File::files(lang_path($locale)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $name = $file->getFilenameWithoutExtension();
            $values = Arr::dot(require $file->getPathname());

            foreach ($values as $key => $value) {
                $catalogue[$name.'.'.$key] = $value ?? true;

                $parts = explode('.', $key);

                while (count($parts) > 1) {
                    array_pop($parts);
                    $catalogue[$name.'.'.implode('.', $parts)] ??= true;
                }
            }
        }

        return $catalogue;
    }

    /** @return list<string> */
    private function referencedKeys(): array
    {
        $directories = [
            app_path(),
            resource_path('views'),
            database_path('seeders'),
            base_path('tests'),
        ];
        $keys = [];

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            foreach (File::allFiles($directory) as $file) {
                $contents = $file->getContents();

                preg_match_all('/(?:__|trans|trans_choice)\(\s*([\'"])([a-z0-9_.-]+)\1/u', $contents, $matches);
                $this->addReferencedKeys($keys, $matches[2]);

                preg_match_all('/Lang::(?:get|has|choice)\(\s*([\'"])([a-z0-9_.-]+)\1/u', $contents, $matches);
                $this->addReferencedKeys($keys, $matches[2]);
            }
        }

        $keys = array_keys($keys);
        sort($keys);

        return $keys;
    }

    /**
     * @param  list<array{string,string,string}>  $problems
     * @param  array<string,true>  $seenProblems
     */
    private function addProblem(array &$problems, array &$seenProblems, string $type, string $locale, string $key): void
    {
        $fingerprint = $type.':'.$locale.':'.$key;

        if (isset($seenProblems[$fingerprint])) {
            return;
        }

        $seenProblems[$fingerprint] = true;
        $problems[] = [$type, $locale, $key];
    }

    /**
     * @param  array<string,true>  $keys
     * @param  list<string>  $matches
     */
    private function addReferencedKeys(array &$keys, array $matches): void
    {
        foreach ($matches as $key) {
            if (str_ends_with($key, '.') || str_ends_with($key, '_')) {
                continue;
            }

            $keys[$key] = true;
        }
    }

    /**
     * @param  list<string>  $locales
     * @return list<array{string,string,string}>
     */
    private function enumLabelProblems(array $locales): array
    {
        $problems = [];
        $originalLocale = app()->getLocale();

        foreach (File::allFiles(app_path('Enums')) as $file) {
            $class = 'App\\Enums\\'.$file->getFilenameWithoutExtension();

            if (! enum_exists($class) || ! is_subclass_of($class, BackedEnum::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (! $reflection->hasMethod('label')) {
                $problems[] = ['enum_label', 'all', $class.'::label'];

                continue;
            }

            foreach ($locales as $locale) {
                app()->setLocale($locale);

                foreach ($class::cases() as $case) {
                    try {
                        $label = $case->label();
                    } catch (Throwable $exception) {
                        $problems[] = ['enum_label', $locale, $class.'::'.$case->name.' '.$exception->getMessage()];

                        continue;
                    }

                    if (! is_string($label) || trim($label) === '' || $this->looksLikeMissingTranslation($label)) {
                        $problems[] = ['enum_label', $locale, $class.'::'.$case->name];
                    }
                }
            }
        }

        app()->setLocale($originalLocale);

        return $problems;
    }

    /**
     * @return list<array{string,string,string}>
     */
    private function catalogueSeedProblems(): array
    {
        $problems = [];

        foreach (['amenity' => AmenityRuleCatalog::amenities(), 'rule' => AmenityRuleCatalog::rules()] as $type => $items) {
            foreach ($items as $item) {
                foreach (config('localization.supported_locales', []) as $locale) {
                    if (! isset($item[$locale]) || trim((string) $item[$locale]) === '') {
                        $problems[] = ['catalog_translation', $locale, $type.'.'.$item['slug']];
                    }
                }
            }
        }

        return $problems;
    }

    /**
     * @param  array<string,array<string,mixed>>  $catalogues
     * @return list<array{string,string,string}>
     */
    private function validationAttributeProblems($catalogues): array
    {
        $required = [
            'about',
            'body',
            'checkIn',
            'checkOut',
            'city',
            'country',
            'currency',
            'description',
            'displayName',
            'email',
            'guestMessage',
            'guestsCount',
            'locale',
            'name',
            'password',
            'password_confirmation',
            'phone',
            'photo',
            'reason',
            'requestedNewCheckout',
            'title',
            'type',
        ];
        $problems = [];

        foreach ($catalogues as $locale => $catalogue) {
            foreach ($required as $attribute) {
                $key = 'validation.attributes.'.$attribute;

                if (! array_key_exists($key, $catalogue) || trim((string) $catalogue[$key]) === '') {
                    $problems[] = ['validation_attribute', $locale, $key];
                }
            }
        }

        return $problems;
    }

    /**
     * @return list<array{string,string,string}>
     */
    private function hardCodedBladeTextProblems(): array
    {
        $problems = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if ($file->getExtension() !== 'php' || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = $this->stripBladeExpressions($file->getContents());

            preg_match_all('/>\s*([^<>{}@]*[A-Za-zА-Яа-яЁё][^<>{}@]*)\s*</u', $contents, $matches);

            foreach ($matches[1] as $text) {
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

                if ($text !== '' && ! $this->isAllowedBladeText($text)) {
                    $problems[] = ['hardcoded_blade_text', 'blade', $file->getRelativePathname().': '.$text];
                }
            }

            preg_match_all(
                '/\b(?:aria-label|label|placeholder|title|alt|heading|description)\s*=\s*(["\'])([^"\']*[A-Za-zА-Яа-яЁё][^"\']*)\1/u',
                $contents,
                $matches,
            );

            foreach ($matches[2] as $text) {
                $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

                if ($text !== '' && ! str_contains($text, '{{') && ! $this->isAllowedBladeText($text)) {
                    $problems[] = ['hardcoded_blade_attribute', 'blade', $file->getRelativePathname().': '.$text];
                }
            }
        }

        return $problems;
    }

    private function stripBladeExpressions(string $contents): string
    {
        $contents = preg_replace('/{{--.*?--}}/s', '', $contents) ?? $contents;
        $contents = preg_replace('/@php.*?@endphp/s', '', $contents) ?? $contents;
        $contents = preg_replace('/{{.*?}}/s', '', $contents) ?? $contents;
        $contents = preg_replace('/{!!.*?!!}/s', '', $contents) ?? $contents;

        return preg_replace('/@[a-zA-Z][\w:.-]*(?:\s*\([^\n]*?\))?/s', '', $contents) ?? $contents;
    }

    private function isAllowedBladeText(string $text): bool
    {
        if (preg_match('/[$\[\](){}]|=>|::|->|\bas\b/u', $text)) {
            return true;
        }

        if (preg_match('/^(rent2gether|r2|EUR|USD|RUB|€|·|-|:)$/u', $text)) {
            return true;
        }

        return preg_match('/^[\d\s.,:;#%+*\/|&€·★-]+$/u', $text) === 1;
    }

    private function looksLikeMissingTranslation(string $value): bool
    {
        return preg_match('/^[a-z0-9_.-]+$/', $value) === 1 && str_contains($value, '.');
    }
}
