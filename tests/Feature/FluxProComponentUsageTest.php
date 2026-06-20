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
            resource_path('views/livewire/host/property-form.blade.php'),
            resource_path('views/livewire/search/sleeping-place-search.blade.php'),
        ];

        foreach ($views as $view) {
            $this->assertStringContainsString('<flux:autocomplete', File::get($view), $view);
        }
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
}
