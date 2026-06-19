<?php

namespace Tests\Feature;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectFoundationTest extends TestCase
{
    public function test_sqlite_is_configured_in_the_example_environment(): void
    {
        $environment = File::get(base_path('.env.example'));

        $this->assertStringContainsString('DB_CONNECTION=sqlite', $environment);
        $this->assertStringContainsString('DB_DATABASE=database/database.sqlite', $environment);
        $this->assertStringContainsString('DB_FOREIGN_KEYS=true', $environment);
    }

    public function test_volt_is_not_installed_or_used(): void
    {
        $this->assertFalse(InstalledVersions::isInstalled('livewire/volt'));

        $files = File::allFiles([
            app_path(),
            resource_path('views'),
            base_path('routes'),
        ]);

        foreach ($files as $file) {
            $contents = $file->getContents();

            $this->assertStringNotContainsString('Livewire\\Volt', $contents, $file->getPathname());
            $this->assertStringNotContainsString('@volt', $contents, $file->getPathname());
        }
    }

    public function test_no_admin_routes_are_registered(): void
    {
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route): bool {
            $name = $route->getName();
            $uri = $route->uri();

            return Str::startsWith($uri, ['admin', 'admin/'])
                || (is_string($name) && Str::startsWith($name, 'admin.'));
        });

        $this->assertCount(0, $adminRoutes, 'Admin routes are not allowed yet.');
    }
}
