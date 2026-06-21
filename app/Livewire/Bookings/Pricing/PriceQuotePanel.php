<?php

namespace App\Livewire\Bookings\Pricing;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PriceQuotePanel extends Component
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
                'nights_count',
                'currency',
                'total_payable',
                'total_without_deposit',
                'deposit_amount',
                'refundable_amount',
                'non_refundable_amount',
                'requires_host_time_approval',
                'pricing_status',
            ])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.pricing.price-quote-panel', [
            'summary' => [
                'quote_number' => $quote->quote_number,
                'nights_count' => (int) $quote->nights_count,
                'total_payable' => $this->money($quote->total_payable, $quote->currency),
                'total_without_deposit' => $this->money($quote->total_without_deposit, $quote->currency),
                'deposit_amount' => $this->money($quote->deposit_amount, $quote->currency),
                'refundable_amount' => $this->money($quote->refundable_amount, $quote->currency),
                'non_refundable_amount' => $this->money($quote->non_refundable_amount, $quote->currency),
                'requires_host_time_approval' => (bool) $quote->requires_host_time_approval,
                'pricing_status' => __('booking_quotes.pricing_statuses.'.$quote->pricing_status),
            ],
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
