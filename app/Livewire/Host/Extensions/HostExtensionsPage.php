<?php

namespace App\Livewire\Host\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostExtensionsPage extends Component
{
    use LoadsBookingExtension;

    public function render(): View
    {
        $data = $this->extensionViewData('page');
        $data['extensions'] = $this->hostExtensions();

        return view('livewire.host.extensions.card', $data);
    }
}
