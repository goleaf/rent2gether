<?php

namespace App\Livewire\Host\Extensions;

use App\Models\BookingExtension;
use Illuminate\Contracts\View\View;

class HostExtensionResponsePanel extends HostExtensionDetailsSheet
{
    public function mount(BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension(extension: $extension);
    }

    public function render(): View
    {
        return view('livewire.host.extensions.card', $this->extensionViewData('response_panel'));
    }
}
