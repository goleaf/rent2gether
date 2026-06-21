<?php

namespace App\Livewire\Bookings\Quotes;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingQuotePanel extends Component
{
    #[Locked]
    public int $quoteId;

    public function mount(int|BookingQuote $quoteId): void
    {
        $this->quoteId = $quoteId instanceof BookingQuote ? $quoteId->id : $quoteId;
    }

    public function render(): View
    {
        return view('livewire.bookings.quotes.booking-quote-panel');
    }
}
