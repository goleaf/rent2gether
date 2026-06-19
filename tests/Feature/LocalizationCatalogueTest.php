<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LocalizationCatalogueTest extends TestCase
{
    /** @var list<string> */
    private const REQUIRED_FILES = [
        'app.php',
        'navigation.php',
        'auth.php',
        'search.php',
        'listing.php',
        'booking.php',
        'host.php',
        'validation.php',
        'statuses.php',
        'notifications.php',
    ];

    public function test_root_language_catalogues_exist_and_have_matching_keys(): void
    {
        $this->assertSame(base_path('lang'), lang_path());

        foreach (self::REQUIRED_FILES as $file) {
            $englishPath = lang_path('en/'.$file);
            $russianPath = lang_path('ru/'.$file);

            $this->assertFileExists($englishPath);
            $this->assertFileExists($russianPath);

            $englishKeys = array_keys(Arr::dot(require $englishPath));
            $russianKeys = array_keys(Arr::dot(require $russianPath));

            sort($englishKeys);
            sort($russianKeys);

            $this->assertSame($englishKeys, $russianKeys, $file.' translation keys differ.');
        }
    }

    public function test_blade_and_livewire_code_use_translation_keys_instead_of_sentences(): void
    {
        $files = [
            ...File::allFiles(resource_path('views')),
            ...File::allFiles(app_path('Livewire')),
            ...File::allFiles(app_path('Http')),
            ...File::allFiles(app_path('Enums')),
        ];

        foreach ($files as $file) {
            $contents = $file->getContents();
            preg_match_all('/__\(\s*([\'\"])(.*?)\1/u', $contents, $matches);

            foreach ($matches[2] as $key) {
                $this->assertMatchesRegularExpression(
                    '/^[a-z0-9_.-]+$/',
                    $key,
                    $file->getPathname().' contains a visible sentence instead of a translation key: '.$key,
                );
            }
        }
    }
}
