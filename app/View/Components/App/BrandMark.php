<?php

namespace App\View\Components\App;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BrandMark extends Component
{
    public string $classes;

    public function __construct(public readonly string $size = 'base')
    {
        $this->classes = $size === 'sm' ? 'size-6 text-[0.625rem]' : 'size-8 text-xs';
    }

    public function render(): View
    {
        return view('components.app.brand-mark', [
            'classes' => $this->classes,
        ]);
    }
}
