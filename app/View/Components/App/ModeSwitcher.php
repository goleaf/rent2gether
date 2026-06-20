<?php

namespace App\View\Components\App;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class ModeSwitcher extends Component
{
    public readonly bool $isHostMode;

    public readonly string $guestHref;

    public readonly string $hostHref;

    public function __construct(private readonly Request $request)
    {
        $locale = app()->getLocale();

        $this->isHostMode = $this->request->routeIs('host.*');
        $this->guestHref = route('home', ['locale' => $locale]);
        $this->hostHref = auth()->check()
            ? route('host.dashboard', ['locale' => $locale])
            : route('auth.login');
    }

    public function render(): View
    {
        return view('components.app.mode-switcher');
    }
}
