<?php

namespace App\Livewire\Bookings\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\BookingExtension;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BookingExtensionQuote extends Component
{
    use LoadsBookingExtension;

    public function mount(BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension(extension: $extension);
    }

    public function render(): View
    {
        return view('livewire.bookings.extensions.card', $this->extensionViewData('quote'));
    }
}
