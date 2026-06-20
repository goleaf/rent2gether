<?php

namespace App\Livewire\Layout;

use App\Services\Localization\LocaleService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $locale = 'en';

    public function mount(): void
    {
        $this->locale = app()->getLocale();
    }

    public function switchLocale(string $locale, LocaleService $locales): void
    {
        $this->locale = $locales->apply($locale, auth()->user());
    }

    public function render(): View
    {
        return view('livewire.layout.language-switcher', [
            'locales' => app(LocaleService::class)->supported(),
        ]);
    }
}
