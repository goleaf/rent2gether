<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BladeLayoutStandardTest extends TestCase
{
    public function test_app_layout_owns_the_shared_page_width(): void
    {
        $layout = File::get(resource_path('views/components/layouts/app.blade.php'));

        $this->assertStringContainsString('<flux:main class="!px-0 !py-0">', $layout);
        $this->assertStringContainsString('mx-auto w-full max-w-5xl px-4 py-4 pb-24 sm:px-6 lg:py-6', $layout);
    }

    public function test_shared_ui_wrappers_define_the_blade_standard(): void
    {
        $this->assertStringContainsString(
            'w-full space-y-5',
            File::get(resource_path('views/components/ui/page.blade.php')),
        );

        $this->assertStringContainsString(
            'space-y-4',
            File::get(resource_path('views/components/ui/section.blade.php')),
        );

        $surface = File::get(resource_path('views/components/ui/surface.blade.php'));

        $this->assertStringContainsString('<flux:card', $surface);
        $this->assertStringContainsString('space-y-4', $surface);
    }

    public function test_livewire_root_wrappers_do_not_define_their_own_page_width(): void
    {
        $allowedPartials = [
            'resources/views/livewire/favorites/favorite-collections-list.blade.php',
        ];

        $offenders = collect(File::allFiles(resource_path('views/livewire')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->reject(function ($file) use ($allowedPartials): bool {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return in_array($relativePath, $allowedPartials, true);
            })
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                $rootLine = collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->map(fn (string $line): string => trim($line))
                    ->first(fn (string $line): bool => $line !== '' && ! str_starts_with($line, '{{--'));

                if (! is_string($rootLine)) {
                    return [];
                }

                $hasPageWidthClass = preg_match(
                    '/^<(div|section)\s+class="[^"]*(mx-auto|max-w-(xs|sm|md|lg|xl|\d+xl)|\bpx-4\b|\bpb-2[48]\b|\bpb-32\b)[^"]*"/',
                    $rootLine,
                ) === 1;

                if (! $hasPageWidthClass) {
                    return [];
                }

                return [$relativePath.':1 '.$rootLine];
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_fixed_action_wrappers_use_the_shared_page_width(): void
    {
        $offenders = collect(File::allFiles(resource_path('views/livewire')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/mx-auto\s+(flex\s+|grid\s+)?max-w-(xl|2xl|3xl)/', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }
}
