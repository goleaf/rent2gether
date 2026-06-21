<?php

namespace App\Livewire\Host\Pricing;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostPayoutPreview extends Component
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
                'host_payout_preview_amount',
                'host_payout_due_at',
            ])
            ->findOrFail($this->quoteId);

        return view('livewire.host.pricing.host-payout-preview', [
            'preview' => [
                'accommodation_after_discount' => $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount, $quote->currency),
                'cleaning_fee' => $this->money($quote->cleaning_fee_amount, $quote->currency),
                'host_payout' => $this->money($quote->host_payout_preview_amount, $quote->currency),
                'payout_date' => $quote->host_payout_due_at?->translatedFormat('d M, H:i'),
            ],
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
