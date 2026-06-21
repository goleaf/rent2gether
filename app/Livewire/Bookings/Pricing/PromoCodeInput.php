<?php

namespace App\Livewire\Bookings\Pricing;

use App\Models\BookingQuote;
use App\Services\Bookings\BookingPriceQuoteService;
use App\Services\Pricing\PromoCodeService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PromoCodeInput extends Component
{
    #[Locked]
    public int $quoteId;

    public string $promoCode = '';

    public ?string $messageKey = null;

    public function mount(int|BookingQuote $quoteId): void
    {
        $this->quoteId = $quoteId instanceof BookingQuote ? $quoteId->id : $quoteId;
        $this->promoCode = (string) BookingQuote::query()->whereKey($this->quoteId)->value('promo_code');
    }

    public function apply(PromoCodeService $promoCodes, BookingPriceQuoteService $quotes): void
    {
        $quote = BookingQuote::query()->with('guest')->findOrFail($this->quoteId);

        if (trim($this->promoCode) === '') {
            $promoCodes->removePromoCode($quote);
            $quotes->recalculateQuote($quote);
            $this->messageKey = 'pricing.messages.promo_removed';

            return;
        }

        $updated = $promoCodes->applyPromoCode($quote, $this->promoCode);
        $quotes->recalculateQuote($updated);
        $this->messageKey = $updated->promo_code_status === 'valid'
            ? 'pricing.messages.promo_applied'
            : 'pricing.messages.promo_invalid';
    }

    public function render(): View
    {
        return view('livewire.bookings.pricing.promo-code-input');
    }
}
