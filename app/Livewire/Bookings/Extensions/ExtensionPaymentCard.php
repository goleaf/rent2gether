<?php

namespace App\Livewire\Bookings\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\BookingExtension;
use App\Services\Bookings\BookingExtensionPaymentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ExtensionPaymentCard extends Component
{
    use LoadsBookingExtension;

    public function mount(BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension(extension: $extension);
    }

    public function markPaid(): void
    {
        $extension = $this->extension();

        if ($extension) {
            app(BookingExtensionPaymentService::class)->markPaid($extension);
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.extensions.card', $this->extensionViewData('payment'));
    }
}
