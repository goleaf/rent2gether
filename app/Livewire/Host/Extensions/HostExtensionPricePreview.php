<?php

namespace App\Livewire\Host\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\BookingExtension;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostExtensionPricePreview extends Component
{
    use LoadsBookingExtension;

    public function mount(BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension(extension: $extension);
    }

    public function render(): View
    {
        return view('livewire.host.extensions.card', $this->extensionViewData('price'));
    }
}
