<?php

namespace App\Livewire\Bookings\Messages;

use App\Models\Booking;
use App\Services\Messaging\ConversationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingMessagesCard extends Component
{
    #[Locked]
    public int $bookingId;

    public function mount(Booking|int $booking): void
    {
        $this->bookingId = $booking instanceof Booking ? $booking->id : $booking;
    }

    public function render(): View
    {
        $conversation = app(ConversationService::class)->getOrCreateForBooking(Booking::query()->findOrFail($this->bookingId));

        return view('livewire.bookings.messages.booking-messages-card', [
            'conversation' => $conversation,
        ]);
    }
}
