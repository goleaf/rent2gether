<?php

namespace App\Livewire\Bookings\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Services\Bookings\BookingExtensionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class GuestExtensionPage extends Component
{
    use LoadsBookingExtension;

    public function mount(Booking|int|null $booking = null, BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension($booking, $extension);
    }

    public function requestExtension(): void
    {
        $this->validate([
            'newCheckOutDate' => ['required', 'date'],
            'guestMessage' => ['nullable', 'string', 'max:1000'],
        ], attributes: __('booking_extensions.validation_attributes'));

        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            throw ValidationException::withMessages([
                'newCheckOutDate' => __('booking_extensions.messages.not_allowed'),
            ]);
        }

        $extension = app(BookingExtensionService::class)->createRequest(Auth::user(), $booking, [
            'new_check_out_date' => $this->newCheckOutDate,
            'guest_message' => $this->guestMessage ?: null,
        ]);

        $this->extensionId = $extension->id;
    }

    public function render(): View
    {
        return view('livewire.bookings.extensions.card', $this->extensionViewData('guest_page'));
    }
}
