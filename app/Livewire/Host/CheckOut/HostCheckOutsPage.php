<?php

namespace App\Livewire\Host\CheckOut;

use Illuminate\View\View;
use Livewire\Component;

class HostCheckOutsPage extends Component
{
    public function render(): View
    {
        return view('livewire.host.check-out.details-sheet', ['checkOut' => null, 'variant' => 'page']);
    }
}
