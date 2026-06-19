<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LocalizedModelContentResolver::class,
            fn (): LocalizedModelContentResolver => new LocalizedModelContentResolver(config('app.fallback_locale')),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([SetLocale::class]);
    }
}
