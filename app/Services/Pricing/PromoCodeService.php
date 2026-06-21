<?php

namespace App\Services\Pricing;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\User;

class PromoCodeService
{
    /**
     * @return array{valid:bool,status:string,message_key:string,promo_code:PromoCode|null}
     */
    public function validatePromoCode(User $guest, BookingQuote $quote, string $code): array
    {
        $promoCode = PromoCode::query()
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $promoCode instanceof PromoCode) {
            return $this->invalid('not_found');
        }

        if (! $promoCode->active) {
            return $this->invalid('inactive', $promoCode);
        }

        if ($promoCode->starts_at !== null && $promoCode->starts_at->isFuture()) {
            return $this->invalid('not_started', $promoCode);
        }

        if ($promoCode->ends_at !== null && $promoCode->ends_at->isPast()) {
            return $this->invalid('expired', $promoCode);
        }

        if ($promoCode->usage_limit !== null && $promoCode->redemptions()->count() >= (int) $promoCode->usage_limit) {
            return $this->invalid('usage_limit_reached', $promoCode);
        }

        if ($promoCode->usage_limit_per_user !== null
            && $promoCode->redemptions()->where('user_id', $guest->id)->count() >= (int) $promoCode->usage_limit_per_user) {
            return $this->invalid('user_limit_reached', $promoCode);
        }

        if ($promoCode->currency !== null && strtoupper($promoCode->currency) !== strtoupper($quote->currency)) {
            return $this->invalid('currency_mismatch', $promoCode);
        }

        if ($promoCode->min_booking_amount !== null && (float) $quote->accommodation_amount < (float) $promoCode->min_booking_amount) {
            return $this->invalid('min_booking_amount', $promoCode);
        }

        if ($promoCode->min_nights !== null && (int) $quote->nights_count < (int) $promoCode->min_nights) {
            return $this->invalid('min_nights', $promoCode);
        }

        if ($promoCode->sleeping_place_id !== null && (int) $promoCode->sleeping_place_id !== (int) $quote->sleeping_place_id) {
            return $this->invalid('sleeping_place_mismatch', $promoCode);
        }

        if ($promoCode->property_id !== null && (int) $promoCode->property_id !== (int) $quote->property_id) {
            return $this->invalid('property_mismatch', $promoCode);
        }

        if ($promoCode->host_user_id !== null && (int) $promoCode->host_user_id !== (int) $quote->host_user_id) {
            return $this->invalid('host_mismatch', $promoCode);
        }

        if ($promoCode->new_guest_only && ! $this->guestIsNew($guest)) {
            return $this->invalid('new_guest_only', $promoCode);
        }

        return [
            'valid' => true,
            'status' => 'valid',
            'message_key' => 'pricing.promo.valid',
            'promo_code' => $promoCode,
        ];
    }

    public function applyPromoCode(BookingQuote $quote, string $code): BookingQuote
    {
        $quote->loadMissing('guest');
        $validation = $this->validatePromoCode($quote->guest, $quote, $code);

        $quote->forceFill([
            'promo_code' => strtoupper(trim($code)),
            'promo_code_status' => $validation['status'],
        ])->save();

        return $quote->refresh();
    }

    public function removePromoCode(BookingQuote $quote): BookingQuote
    {
        $quote->forceFill([
            'promo_code' => null,
            'promo_code_status' => null,
        ])->save();

        return $quote->refresh();
    }

    public function calculatePromoDiscount(BookingQuote $quote, PromoCode $promoCode): float
    {
        $baseAmount = $this->money($quote->accommodation_amount);

        return match ($promoCode->value_type) {
            PromoCode::VALUE_PERCENT => $this->money($baseAmount * ((float) $promoCode->value / 100)),
            PromoCode::VALUE_FIXED_AMOUNT => $this->money((float) $promoCode->value),
            PromoCode::VALUE_FIXED_PRICE => $this->money(max(0, $baseAmount - (float) $promoCode->value)),
            default => 0.0,
        };
    }

    public function recordRedemption(BookingQuote $quote, Booking $booking): PromoCodeRedemption
    {
        $promoCode = PromoCode::query()
            ->where('code', strtoupper((string) $quote->promo_code))
            ->firstOrFail();

        return PromoCodeRedemption::query()->create([
            'promo_code_id' => $promoCode->id,
            'user_id' => $quote->user_id,
            'booking_quote_id' => $quote->id,
            'booking_id' => $booking->id,
            'discount_amount' => $this->calculatePromoDiscount($quote, $promoCode),
            'currency' => $quote->currency,
            'redeemed_at' => now(),
        ]);
    }

    /**
     * @return array{valid:bool,status:string,message_key:string,promo_code:PromoCode|null}
     */
    private function invalid(string $status, ?PromoCode $promoCode = null): array
    {
        return [
            'valid' => false,
            'status' => $status,
            'message_key' => 'pricing.promo.'.$status,
            'promo_code' => $promoCode,
        ];
    }

    private function guestIsNew(User $guest): bool
    {
        return ! Booking::query()
            ->where('guest_user_id', $guest->id)
            ->where('status', BookingStatus::Completed->value)
            ->exists();
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
