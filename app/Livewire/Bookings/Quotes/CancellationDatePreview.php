<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CancellationDatePreview extends Component
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
            ->select(['id', 'free_cancellation_until', 'cancellation_penalty_starts_at'])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.quotes.cancellation-date-preview', [
            'preview' => [
                'free_cancellation_until' => $quote->free_cancellation_until?->translatedFormat('d M, H:i'),
                'cancellation_penalty_starts_at' => $quote->cancellation_penalty_starts_at?->translatedFormat('d M, H:i'),
            ],
        ]);
    }
}
