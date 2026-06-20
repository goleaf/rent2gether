<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FluxProComponentUsageTest extends TestCase
{
    public function test_known_typeahead_views_use_flux_autocomplete(): void
    {
        $views = [
            resource_path('views/livewire/catalog/amenity-picker.blade.php'),
            resource_path('views/livewire/catalog/rule-picker.blade.php'),
            resource_path('views/livewire/geo/city-autocomplete.blade.php'),
            resource_path('views/livewire/geo/partials/country-city-autocomplete.blade.php'),
            resource_path('views/livewire/search/sleeping-place-search.blade.php'),
        ];

        foreach ($views as $view) {
            $this->assertStringContainsString('<flux:autocomplete', File::get($view), $view);
        }

        $this->assertStringContainsString(
            'livewire.geo.partials.country-city-autocomplete',
            File::get(resource_path('views/livewire/host/property-form.blade.php')),
        );
    }

    public function test_collapsible_blade_sections_use_flux_accordion(): void
    {
        $offenders = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/<\s*\/?\s*(details|summary)\b/i', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_blade_action_and_form_controls_use_flux_components(): void
    {
        $offenders = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/<\s*\/?\s*(button|input|select|textarea|option|table)\b/i', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_listing_chip_and_loading_surfaces_use_flux_components(): void
    {
        $this->assertStringContainsString(
            '<flux:badge',
            File::get(resource_path('views/components/listings/card-amenities.blade.php')),
        );

        $this->assertStringContainsString(
            '<flux:badge',
            File::get(resource_path('views/components/listings/card-rules.blade.php')),
        );

        $skeleton = File::get(resource_path('views/components/listings/card-skeleton.blade.php'));

        $this->assertStringContainsString('<flux:skeleton.group', $skeleton);
        $this->assertStringContainsString('<flux:skeleton.line', $skeleton);
        $this->assertStringNotContainsString('animate-pulse', $skeleton);
    }

    public function test_media_manager_uses_flux_pro_file_upload_components(): void
    {
        $view = File::get(resource_path('views/livewire/media/manage-media.blade.php'));

        $this->assertStringContainsString('<flux:file-upload', $view);
        $this->assertStringContainsString('<flux:file-upload.dropzone', $view);
        $this->assertStringContainsString('<flux:file-item', $view);
        $this->assertStringContainsString('<flux:file-item.remove', $view);
        $this->assertStringNotContainsString('type="file"', $view);
    }

    public function test_root_level_custom_rounded_panels_are_not_used_for_livewire_and_shared_components(): void
    {
        $offenders = collect([
            resource_path('views/livewire'),
            resource_path('views/components'),
        ])
            ->flatMap(fn (string $path) => File::allFiles($path))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/^<\s*(article|section|div)\s+class="[^"]*rounded-lg[^"]*border/i', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }
}
