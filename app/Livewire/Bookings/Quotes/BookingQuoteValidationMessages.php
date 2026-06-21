<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingQuoteValidationMessages extends Component
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
            ->with(['validationResults' => fn ($query) => $query->where('visible_to_guest', true)->orderByDesc('blocking')->orderBy('id')])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.quotes.booking-quote-validation-messages', [
            'messages' => $quote->validationResults
                ->map(fn ($result): array => [
                    'severity' => $result->severity,
                    'blocking' => (bool) $result->blocking,
                    'message' => __($result->message_key, $result->message_params_json ?? []),
                ])
                ->values()
                ->all(),
        ]);
    }
}
