<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BladeNoPhpDirectiveTest extends TestCase
{
    public function test_blade_templates_do_not_use_php_directives(): void
    {
        $offenders = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => str_contains($line, '@php') || str_contains($line, '@endphp'))
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }
}
