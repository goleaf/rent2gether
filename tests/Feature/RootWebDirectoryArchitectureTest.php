<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootWebDirectoryArchitectureTest extends TestCase
{
    public function test_application_uses_project_root_as_web_directory(): void
    {
        $this->assertSame(base_path(), public_path());
        $this->assertFileExists(base_path('index.php'));
        $this->assertFileExists(base_path('.htaccess'));
        $this->assertFileExists(base_path('favicon.svg'));
        $this->assertFileExists(base_path('robots.txt'));
        $this->assertFileExists(base_path('LocalValetDriver.php'));
        $this->assertDirectoryDoesNotExist(base_path('public'));
    }

    public function test_vite_and_public_storage_are_configured_for_root_web_directory(): void
    {
        $viteConfig = (string) file_get_contents(base_path('vite.config.js'));
        $htaccess = (string) file_get_contents(base_path('.htaccess'));

        $this->assertStringContainsString("publicDirectory: '.'", $viteConfig);
        $this->assertStringContainsString('RewriteRule ^storage/(.+)$ storage/app/public/$1 [END]', $htaccess);
        $this->assertStringContainsString('RewriteRule ^storage/(?:app|framework|logs)(?:/|$) - [F,L]', $htaccess);
        $this->assertStringContainsString('RewriteRule ^(?:app|bootstrap|config|database|docs|lang|node_modules|resources|routes|scripts|tests|vendor)(?:/|$) - [F,L]', $htaccess);
        $this->assertSame([], config('filesystems.links'));
    }

    public function test_herd_valet_driver_only_serves_allowlisted_root_static_files(): void
    {
        $driver = (string) file_get_contents(base_path('LocalValetDriver.php'));

        $this->assertStringContainsString('in_array($uri, [\'/favicon.ico\', \'/favicon.svg\', \'/robots.txt\'], true)', $driver);
        $this->assertStringContainsString('str_starts_with($uri, \'/build/\')', $driver);
        $this->assertStringContainsString('str_starts_with($uri, \'/storage/\')', $driver);
        $this->assertStringContainsString("'/storage/app/public/'", $driver);
        $this->assertStringContainsString('containsUnsafePathSegment', $driver);
    }
}
