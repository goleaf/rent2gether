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

    public function test_livewire_upload_surfaces_use_flux_pro_file_upload_components(): void
    {
        $views = [
            resource_path('views/livewire/account/profile-setup-page.blade.php'),
            resource_path('views/livewire/checkin/problem-report.blade.php'),
            resource_path('views/livewire/complaints/create-complaint.blade.php'),
            resource_path('views/livewire/host/partials/host-profile-section.blade.php'),
            resource_path('views/livewire/host/property-form.blade.php'),
            resource_path('views/livewire/host/room-form.blade.php'),
            resource_path('views/livewire/host/sleeping-place-form.blade.php'),
            resource_path('views/livewire/messages/chat-window.blade.php'),
            resource_path('views/livewire/reviews/create-review.blade.php'),
        ];

        foreach ($views as $viewPath) {
            $view = File::get($viewPath);

            $this->assertStringContainsString('<flux:file-upload', $view, $viewPath);
            $this->assertStringContainsString('<flux:file-upload.dropzone', $view, $viewPath);
            $this->assertStringNotContainsString('type="file"', $view, $viewPath);
        }
    }

    public function test_frontend_assets_use_scss_and_flux_pro_styles(): void
    {
        $this->assertFileExists(resource_path('css/app.scss'));
        $this->assertFileDoesNotExist(resource_path('css/app.css'));
        $this->assertDirectoryDoesNotExist(resource_path('js'));

        $stylesheet = File::get(resource_path('css/app.scss'));

        $this->assertStringContainsString("@use 'tailwindcss';", $stylesheet);
        $this->assertStringContainsString("@import '../../vendor/livewire/flux/dist/flux.css';", $stylesheet);
        $this->assertStringContainsString("@import '../../vendor/livewire/flux-pro/dist/editor.css';", $stylesheet);

        $this->assertStringContainsString(
            "input: ['resources/css/app.scss']",
            File::get(base_path('vite.config.js')),
        );

        $this->assertStringContainsString(
            "'@tailwindcss/postcss': {}",
            File::get(base_path('postcss.config.mjs')),
        );

        foreach ([
            resource_path('views/components/layouts/app.blade.php'),
            resource_path('views/components/layouts/guest.blade.php'),
        ] as $layout) {
            $layoutContents = File::get($layout);

            $this->assertStringContainsString("@vite('resources/css/app.scss')", $layoutContents);
            $this->assertStringNotContainsString('resources/css/app.css', $layoutContents);
            $this->assertStringNotContainsString('resources/js/app.js', $layoutContents);
        }
    }

    public function test_flux_ghost_buttons_use_visible_secondary_surface(): void
    {
        $stylesheet = File::get(resource_path('css/app.scss'));

        $this->assertStringContainsString('[data-flux-button].bg-transparent.text-zinc-800:not(.absolute):not(.fixed)', $stylesheet);
        $this->assertStringContainsString('background-color: #f0f9ff;', $stylesheet);
        $this->assertStringContainsString('color: #075985;', $stylesheet);
        $this->assertStringContainsString('background-color: rgba(56, 189, 248, 0.12);', $stylesheet);
        $this->assertFileDoesNotExist(resource_path('views/flux/button/index.blade.php'));
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
