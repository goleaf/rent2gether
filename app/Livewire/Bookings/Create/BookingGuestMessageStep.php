<?php

namespace App\Livewire\Bookings\Create;

use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingGuestMessageStep extends Component
{
    #[Locked]
    public int $bookingId;

    public string $guestMessage = '';

    public function mount(int|Booking $bookingId): void
    {
        $booking = $bookingId instanceof Booking
            ? $bookingId
            : Booking::query()->select(['id', 'guest_message'])->findOrFail($bookingId);
        $this->bookingId = $booking->id;
        $this->guestMessage = (string) $booking->guest_message;
    }

    public function saveMessage(): void
    {
        $this->validate([
            'guestMessage' => ['nullable', 'string', 'max:1000'],
        ]);

        Booking::query()->whereKey($this->bookingId)->update(['guest_message' => $this->guestMessage]);
    }

    public function render(): View
    {
        return view('livewire.bookings.create.booking-guest-message-step');
    }
}
