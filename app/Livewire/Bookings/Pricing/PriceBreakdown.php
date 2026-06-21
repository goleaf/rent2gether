<?php

namespace App\Livewire\Bookings\Pricing;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PriceBreakdown extends Component
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
                'currency',
                'accommodation_amount',
                'discount_amount',
                'cleaning_fee_amount',
                'service_fee_amount',
                'tax_amount',
                'city_fee_amount',
                'deposit_amount',
                'total_without_deposit',
                'total_payable',
                'refundable_amount',
                'non_refundable_amount',
            ])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.pricing.price-breakdown', [
            'rows' => [
                ['label' => __('pricing.fields.amount_before_discount'), 'amount' => $this->money($quote->accommodation_amount, $quote->currency)],
                ['label' => __('pricing.fields.discount_amount'), 'amount' => $this->money($quote->discount_amount, $quote->currency)],
                ['label' => __('pricing.fields.cleaning_fee'), 'amount' => $this->money($quote->cleaning_fee_amount, $quote->currency)],
                ['label' => __('pricing.fields.service_fee'), 'amount' => $this->money($quote->service_fee_amount, $quote->currency)],
                ['label' => __('pricing.fields.deposit'), 'amount' => $this->money($quote->deposit_amount, $quote->currency)],
                ['label' => __('pricing.fields.total_without_deposit'), 'amount' => $this->money($quote->total_without_deposit, $quote->currency)],
                ['label' => __('pricing.fields.total_payable'), 'amount' => $this->money($quote->total_payable, $quote->currency)],
                ['label' => __('pricing.fields.refundable_amount'), 'amount' => $this->money($quote->refundable_amount, $quote->currency)],
                ['label' => __('pricing.fields.non_refundable_amount'), 'amount' => $this->money($quote->non_refundable_amount, $quote->currency)],
            ],
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
