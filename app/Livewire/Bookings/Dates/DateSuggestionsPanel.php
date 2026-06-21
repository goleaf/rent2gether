<?php

namespace App\Livewire\Bookings\Dates;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DateSuggestionsPanel extends Component
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
            ->with(['suggestions' => fn ($query) => $query->orderBy('sort_order')])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.dates.date-suggestions-panel', [
            'suggestions' => $quote->suggestions
                ->map(fn ($suggestion): array => [
                    'type' => $suggestion->suggestion_type,
                    'message' => __($suggestion->message_key),
                    'check_in' => $suggestion->check_in_date?->toDateString(),
                    'check_out' => $suggestion->check_out_date?->toDateString(),
                    'nights' => $suggestion->nights_count,
                    'price' => $suggestion->price_preview_amount === null ? null : Number::currency((float) $suggestion->price_preview_amount, $suggestion->currency ?: $quote->currency, app()->getLocale()),
                ])
                ->values()
                ->all(),
        ]);
    }
}
