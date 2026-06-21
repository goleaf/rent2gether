<?php

namespace App\Livewire\Bookings\Create;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Services\Bookings\BookingCreationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingCreatePage extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public ?int $quoteId = null;

    #[Locked]
    public ?int $bookingId = null;

    public int $step = 1;

    public function mount(int|BookingQuote|Booking|null $quoteId = null, ?int $bookingId = null): void
    {
        if ($quoteId instanceof Booking) {
            $this->bookingId = $quoteId->id;

            return;
        }

        $this->quoteId = $quoteId instanceof BookingQuote ? $quoteId->id : $quoteId;
        $this->bookingId = $bookingId;
    }

    public function confirmInstantBooking(BookingCreationService $creation): void
    {
        if (! $this->quoteId) {
            return;
        }

        $quote = BookingQuote::query()->with('guest')->findOrFail($this->quoteId);
        $booking = $creation->createInstantBooking($quote->guest, $quote, [
            'guest_agreed_to_rules' => true,
        ]);

        $this->bookingId = $booking->id;
        $this->step = 5;
    }

    public function render(): View
    {
        $booking = $this->bookingId ? $this->loadBooking($this->bookingId) : null;
        $quote = $this->quoteId && ! $booking
            ? BookingQuote::query()
                ->select(['id', 'quote_number', 'status', 'check_in_date', 'check_out_date', 'nights_count', 'total_payable', 'deposit_amount', 'currency'])
                ->findOrFail($this->quoteId)
            : null;

        return view('livewire.bookings.create.booking-create-page', [
            'summary' => $booking ? $this->bookingSummary($booking) : null,
            'quote' => $quote ? [
                'quote_number' => $quote->quote_number,
                'total_payable' => Number::currency((float) $quote->total_payable, $quote->currency, app()->getLocale()),
            ] : null,
        ]);
    }
}
