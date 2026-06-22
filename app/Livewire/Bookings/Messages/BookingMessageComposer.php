<?php

namespace App\Livewire\Bookings\Messages;

use App\Models\Booking;
use App\Services\Messaging\ConversationMessageService;
use App\Services\Messaging\ConversationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingMessageComposer extends Component
{
    #[Locked]
    public int $bookingId;

    public string $body = '';

    public function mount(Booking|int $booking): void
    {
        $this->bookingId = $booking instanceof Booking ? $booking->id : $booking;
    }

    public function send(): void
    {
        $validated = $this->validate([
            'body' => ['required', 'string', 'max:5000'],
        ], attributes: app('translator')->get('messages.validation_attributes'));
        $booking = Booking::query()->findOrFail($this->bookingId);
        $conversation = app(ConversationService::class)->getOrCreateForBooking($booking);

        app(ConversationMessageService::class)->sendText(auth()->user(), $conversation, $validated['body'], [
            'source_type' => 'booking',
            'source_id' => $booking->id,
        ]);

        $this->body = '';
    }

    public function render(): View
    {
        return view('livewire.bookings.messages.booking-message-composer');
    }
}
