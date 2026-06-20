<?php

namespace Tests\Feature;

use BackedEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LocalizationCatalogueTest extends TestCase
{
    /** @var list<string> */
    private const REQUIRED_FILES = [
        'account.php',
        'app.php',
        'auth.php',
        'availability.php',
        'search.php',
        'compatibility.php',
        'decision.php',
        'listing.php',
        'booking.php',
        'host.php',
        'media.php',
        'messages.php',
        'navigation.php',
        'notifications.php',
        'preferences.php',
        'shell.php',
        'validation.php',
        'statuses.php',
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

    public function test_all_backed_enums_expose_translated_labels(): void
    {
        $originalLocale = app()->getLocale();

        try {
            foreach (File::allFiles(app_path('Enums')) as $file) {
                $class = 'App\\Enums\\'.$file->getFilenameWithoutExtension();

                if (! enum_exists($class) || ! is_subclass_of($class, BackedEnum::class)) {
                    continue;
                }

                $this->assertTrue(method_exists($class, 'label'), $class.' is missing label().');

                foreach (['en', 'ru'] as $locale) {
                    app()->setLocale($locale);

                    foreach ($class::cases() as $case) {
                        $label = $case->label();

                        $this->assertNotSame('', trim($label), $class.'::'.$case->name.' has an empty '.$locale.' label.');
                        $this->assertDoesNotMatchRegularExpression('/^[a-z0-9_.-]+$/', $label, $class.'::'.$case->name.' returns a missing key in '.$locale.'.');
                    }
                }
            }
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function test_validation_attributes_are_translated_for_core_form_fields(): void
    {
        $required = [
            'email',
            'password',
            'displayName',
            'phone',
            'city',
            'country',
            'checkIn',
            'checkOut',
            'guestMessage',
            'requestedNewCheckout',
            'photo',
            'title',
            'description',
            'reason',
        ];

        foreach (['en', 'ru'] as $locale) {
            $attributes = __('validation.attributes', [], $locale);
            $this->assertIsArray($attributes);

            foreach ($required as $attribute) {
                $this->assertArrayHasKey($attribute, $attributes, $locale.' validation attribute missing: '.$attribute);
                $this->assertNotSame('', trim((string) $attributes[$attribute]));
            }
        }
    }

    public function test_missing_translation_command_reports_clean_catalogue(): void
    {
        $this->assertSame(0, Artisan::call('translations:missing'));
        $this->assertStringContainsString('All', Artisan::output());
    }
}
