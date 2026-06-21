<?php

namespace App\Livewire\Bookings\Pricing;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FeeBreakdown extends Component
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
            ->select(['id', 'currency'])
            ->with(['lines' => fn ($query) => $query
                ->select(['id', 'booking_quote_id', 'line_type', 'label_key', 'amount', 'currency', 'sort_order'])
                ->where('is_fee', true)
                ->orderBy('sort_order')])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.pricing.fee-breakdown', [
            'lines' => $quote->lines->map(fn ($line): array => [
                'label' => __($line->label_key),
                'amount' => $this->money($line->amount, $line->currency ?: $quote->currency),
            ])->all(),
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
