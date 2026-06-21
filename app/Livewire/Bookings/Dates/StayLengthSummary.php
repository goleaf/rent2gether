<?php

namespace App\Livewire\Bookings\Dates;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class StayLengthSummary extends Component
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
            ->select(['id', 'nights_count', 'chargeable_days_count', 'calendar_presence_days_count'])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.dates.stay-length-summary', [
            'summary' => [
                'nights' => (int) $quote->nights_count,
                'chargeable_days' => (int) $quote->chargeable_days_count,
                'calendar_presence_days' => (int) $quote->calendar_presence_days_count,
            ],
        ]);
    }
}
