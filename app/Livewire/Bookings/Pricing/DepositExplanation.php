<?php

namespace App\Livewire\Bookings\Pricing;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DepositExplanation extends Component
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
            ->select(['id', 'deposit_amount', 'refundable_amount', 'currency'])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.pricing.deposit-explanation', [
            'deposit' => [
                'amount' => $this->money($quote->deposit_amount, $quote->currency),
                'refundable' => $this->money($quote->refundable_amount, $quote->currency),
            ],
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
