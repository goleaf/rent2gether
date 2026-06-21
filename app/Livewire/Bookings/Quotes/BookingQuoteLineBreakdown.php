<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingQuoteLineBreakdown extends Component
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
            ->with(['lines' => fn ($query) => $query->orderBy('sort_order')])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.quotes.booking-quote-line-breakdown', [
            'lines' => $quote->lines
                ->map(fn ($line): array => [
                    'type' => $line->line_type,
                    'label' => __($line->label_key),
                    'date' => $line->date?->translatedFormat('d M'),
                    'amount' => Number::currency((float) $line->amount, $line->currency, app()->getLocale()),
                    'is_discount' => (bool) $line->is_discount,
                    'is_deposit' => (bool) $line->is_deposit,
                    'is_refundable' => (bool) $line->is_refundable,
                ])
                ->values()
                ->all(),
        ]);
    }
}
