<?php

namespace App\View\Components\App;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class LocaleSwitcher extends Component
{
    /** @var list<string> */
    public readonly array $locales;

    public function __construct(private readonly Request $request)
    {
        $this->locales = config('localization.supported_locales');
    }

    public function urlFor(string $locale): string
    {
        $route = $this->request->route();

        if (! $route || ! $route->getName() || ! array_key_exists('locale', $route->parameters())) {
            return route('home', ['locale' => $locale]);
        }

        return route($route->getName(), [
            ...$route->parameters(),
            ...$this->request->query(),
            'locale' => $locale,
        ]);
    }

    public function render(): View
    {
        return view('components.app.locale-switcher');
    }
}
