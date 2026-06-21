<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TimelineDatePreview extends Component
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
            ->with(['timelineDates' => fn ($query) => $query->orderBy('scheduled_at')])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.quotes.timeline-date-preview', [
            'dates' => $quote->timelineDates
                ->map(fn ($date): array => [
                    'event_key' => $date->event_key,
                    'label' => __('booking_quotes.timeline.'.$date->event_key),
                    'scheduled_at' => $date->scheduled_at?->translatedFormat('d M, H:i'),
                    'status' => __('booking_quotes.timeline_statuses.'.$date->status),
                ])
                ->values()
                ->all(),
        ]);
    }
}
