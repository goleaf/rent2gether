<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use App\Services\Bookings\BookingQuoteExpirationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class QuoteExpiredBanner extends Component
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
            ->select(['id', 'status', 'expires_at'])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.quotes.quote-expired-banner', [
            'expired' => app(BookingQuoteExpirationService::class)->isExpired($quote),
            'expiresAt' => $quote->expires_at?->translatedFormat('d M, H:i'),
        ]);
    }
}
