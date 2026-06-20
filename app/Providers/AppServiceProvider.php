<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Services\Localization\LocalizedModelContentResolver;
use App\View\Components\App\BrandMark;
use App\View\Components\App\LocaleSwitcher;
use App\View\Components\App\MobileNav;
use App\View\Components\App\ModeSwitcher;
use App\View\Components\Host\PublicCard;
use App\View\Components\Listings\Card;
use App\View\Components\Listings\CardPrice;
use Illuminate\Support\Facades\Blade;
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
            fn (): LocalizedModelContentResolver => new LocalizedModelContentResolver(config('localization.fallback_locale')),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component(BrandMark::class, 'app.brand-mark');
        Blade::component(LocaleSwitcher::class, 'app.locale-switcher');
        Blade::component(MobileNav::class, 'app.mobile-nav');
        Blade::component(ModeSwitcher::class, 'app.mode-switcher');
        Blade::component(PublicCard::class, 'host.public-card');
        Blade::component(Card::class, 'listings.card');
        Blade::component(CardPrice::class, 'listings.card-price');

        Livewire::addPersistentMiddleware([SetLocale::class]);
    }
}
