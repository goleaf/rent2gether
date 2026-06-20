<?php

namespace App\Livewire\Host\Properties;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyCreatePage extends Component
{
    public function render(): View
    {
        return view('livewire.host.properties.property-create-page');
    }
}
