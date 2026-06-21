<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingQuoteSummary extends Component
{
    #[Locked]
    public int $quoteId;

    public function mount(int|BookingQuote $quoteId): void
    {
        $this->quoteId = $quoteId instanceof BookingQuote ? $quoteId->id : $quoteId;
    }

    public function render(): View
    {
        $quote = BookingQuote::query()
            ->select([
                'id',
                'quote_number',
                'status',
                'availability_status',
                'validation_status',
                'pricing_status',
                'currency',
                'total_payable',
                'total_without_deposit',
                'refundable_amount',
                'non_refundable_amount',
                'deposit_amount',
                'expires_at',
            ])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.quotes.booking-quote-summary', [
            'summary' => [
                'quote_number' => $quote->quote_number,
                'status' => __('booking_quotes.statuses.'.$quote->status),
                'availability_status' => __('booking_quotes.availability_statuses.'.$quote->availability_status),
                'validation_status' => __('booking_quotes.validation_statuses.'.$quote->validation_status),
                'pricing_status' => __('booking_quotes.pricing_statuses.'.$quote->pricing_status),
                'total_payable' => $this->money($quote->total_payable, $quote->currency),
                'total_without_deposit' => $this->money($quote->total_without_deposit, $quote->currency),
                'refundable_amount' => $this->money($quote->refundable_amount, $quote->currency),
                'non_refundable_amount' => $this->money($quote->non_refundable_amount, $quote->currency),
                'deposit_amount' => $this->money($quote->deposit_amount, $quote->currency),
                'expires_at' => $quote->expires_at?->translatedFormat('d M, H:i'),
            ],
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
