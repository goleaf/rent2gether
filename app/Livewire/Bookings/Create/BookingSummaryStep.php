<?php

namespace App\Livewire\Bookings\Create;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingSummaryStep extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public ?int $bookingId = null;

    #[Locked]
    public ?int $quoteId = null;

    public function mount(int|Booking|BookingQuote|null $bookingId = null, ?int $quoteId = null): void
    {
        if ($bookingId instanceof Booking) {
            $this->bookingId = $bookingId->id;

            return;
        }

        if ($bookingId instanceof BookingQuote) {
            $this->quoteId = $bookingId->id;

            return;
        }

        $this->bookingId = $bookingId;
        $this->quoteId = $quoteId;
    }

    public function render(): View
    {
        $summary = null;

        if ($this->bookingId) {
            $summary = $this->bookingSummary($this->loadBooking($this->bookingId));
        } elseif ($this->quoteId) {
            $quote = BookingQuote::query()
                ->select(['id', 'quote_number', 'check_in_date', 'check_out_date', 'nights_count', 'total_payable', 'deposit_amount', 'currency'])
                ->findOrFail($this->quoteId);
            $summary = [
                'booking_number' => $quote->quote_number,
                'dates' => $quote->check_in_date?->translatedFormat('d M').' - '.$quote->check_out_date?->translatedFormat('d M'),
                'nights_count' => (int) $quote->nights_count,
                'total_payable' => Number::currency((float) $quote->total_payable, $quote->currency, app()->getLocale()),
                'deposit_amount' => Number::currency((float) $quote->deposit_amount, $quote->currency, app()->getLocale()),
            ];
        }

        return view('livewire.bookings.create.booking-summary-step', [
            'summary' => $summary,
        ]);
    }
}
