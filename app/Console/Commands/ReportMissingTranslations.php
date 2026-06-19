<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

#[Signature('translations:missing')]
#[Description('Report translation files and keys missing from a supported locale')]
class ReportMissingTranslations extends Command
{
    public function handle(): int
    {
        $locales = config('localization.supported_locales');
        $catalogues = collect($locales)->mapWithKeys(
            fn (string $locale): array => [$locale => $this->catalogue($locale)],
        );
        $expectedKeys = $catalogues->flatMap(fn (array $catalogue): array => array_keys($catalogue))->unique()->sort();
        $missing = collect();

        foreach ($catalogues as $locale => $catalogue) {
            foreach ($expectedKeys as $key) {
                if (! array_key_exists($key, $catalogue)) {
                    $missing->push([$locale, $key]);
                }
            }
        }

        foreach ($this->referencedKeys() as $key) {
            foreach ($catalogues as $locale => $catalogue) {
                if (! array_key_exists($key, $catalogue)) {
                    $missing->push([$locale, $key]);
                }
            }
        }

        $missing = $missing->unique(fn (array $item): string => implode(':', $item))->values();

        if ($missing->isNotEmpty()) {
            $this->components->error(__('app.translation_report.missing'));
            $this->table(
                [__('app.translation_report.locale'), __('app.translation_report.key')],
                $missing->all(),
            );

            return self::FAILURE;
        }

        $this->components->info(__('app.translation_report.complete', ['count' => $expectedKeys->count()]));

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function catalogue(string $locale): array
    {
        return collect(File::files(lang_path($locale)))
            ->filter(fn ($file): bool => $file->getExtension() === 'php')
            ->flatMap(function ($file): array {
                $name = $file->getFilenameWithoutExtension();

                return collect(Arr::dot(require $file->getPathname()))
                    ->mapWithKeys(fn (mixed $value, string $key): array => [$name.'.'.$key => $value])
                    ->all();
            })
            ->all();
    }

    /** @return list<string> */
    private function referencedKeys(): array
    {
        $directories = [app_path(), resource_path('views')];
        $keys = collect();

        foreach ($directories as $directory) {
            foreach (File::allFiles($directory) as $file) {
                preg_match_all('/(?:__|trans)\(\s*([\'\"])([a-z0-9_.-]+)\1\s*[,)]/u', $file->getContents(), $matches);
                $keys->push(...$matches[2]);
            }
        }

        return $keys->unique()->sort()->values()->all();
    }
}
