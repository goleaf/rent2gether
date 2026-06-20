<?php

namespace App\Livewire\Home;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HomePage extends Component
{
    public string $destination = '';

    public function render(): View
    {
        return view('livewire.home.home-page');
    }
}
