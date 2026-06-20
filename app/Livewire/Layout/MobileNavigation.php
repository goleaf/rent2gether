<?php

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class MobileNavigation extends Component
{
    public function render(): View
    {
        return view('livewire.layout.mobile-navigation');
    }
}
