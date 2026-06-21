<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationPriceLine;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingRelocationPriceService
{
    public function calculateOldRemainingValue(BookingRelocation $relocation): float
    {
        $nights = $this->remainingNights($relocation);
        $booking = $relocation->originalBooking()->first();
        $price = (float) ($booking?->price_per_night ?: $relocation->currentSleepingPlace?->base_price_per_night ?: $relocation->currentSleepingPlace?->base_price ?: 0);

        return $this->money($nights * $price);
    }

    public function calculateNewRemainingValue(BookingRelocation $relocation): float
    {
        $nights = $this->remainingNights($relocation);
        $place = $relocation->newSleepingPlace()->first();
        $price = (float) ($place?->base_price_per_night ?: $place?->base_price ?: 0);

        return $this->money($nights * $price);
    }

    public function calculateDifference(BookingRelocation $relocation): float
    {
        return $this->money((float) $relocation->new_remaining_value_amount - (float) $relocation->old_remaining_value_amount);
    }

    public function calculateAdditionalPayment(BookingRelocation $relocation): float
    {
        if ($this->calculateWhoPays($relocation) !== 'guest') {
            return 0.0;
        }

        return $this->money(max(0, (float) $relocation->price_difference_amount));
    }

    public function calculateRefundAmount(BookingRelocation $relocation): float
    {
        if ((float) $relocation->price_difference_amount >= 0) {
            return 0.0;
        }

        return $this->money(abs((float) $relocation->price_difference_amount));
    }

    public function calculateDepositDifference(BookingRelocation $relocation): float
    {
        $oldDeposit = (float) ($relocation->currentSleepingPlace?->deposit_amount ?: 0);
        $newDeposit = (float) ($relocation->newSleepingPlace?->deposit_amount ?: 0);

        return $this->money(max(0, $newDeposit - $oldDeposit));
    }

    public function calculateCleaningFeeDifference(BookingRelocation $relocation): float
    {
        return 0.0;
    }

    public function calculateServiceFeeDifference(BookingRelocation $relocation): float
    {
        return $this->money((float) $relocation->additional_payment_amount * 0.05);
    }

    public function calculateHostPayoutDifference(BookingRelocation $relocation): float
    {
        return $this->money((float) $relocation->price_difference_amount);
    }

    public function calculateWhoPays(BookingRelocation $relocation): string
    {
        if ((float) $relocation->price_difference_amount < 0) {
            return 'refund_to_guest';
        }

        if (in_array($relocation->reason, ['breakdown', 'safety_issue', 'listing_mismatch', 'maintenance_issue', 'dirty_place', 'mold', 'insects', 'missing_amenity', 'no_hot_water', 'no_heating', 'no_wifi', 'complaint_resolution', 'host_offered_another_place'], true)) {
            return 'no_extra_charge';
        }

        return (float) $relocation->price_difference_amount > 0 ? 'guest' : 'no_extra_charge';
    }

    /**
     * @return Collection<int, BookingRelocationPriceLine>
     */
    public function buildPriceLines(BookingRelocation $relocation): Collection
    {
        return app(BookingRelocationLineService::class)->rebuildLines($relocation);
    }

    /**
     * @return array<string, mixed>
     */
    public function priceData(BookingRelocation $relocation): array
    {
        $old = $this->calculateOldRemainingValue($relocation);
        $new = $this->calculateNewRemainingValue($relocation);
        $difference = $this->money($new - $old);

        $relocation->forceFill([
            'old_remaining_value_amount' => $old,
            'new_remaining_value_amount' => $new,
            'price_difference_amount' => $difference,
        ]);

        $payer = $this->calculateWhoPays($relocation);
        $additionalPayment = $payer === 'guest' ? max(0, $difference) : 0.0;
        $refund = $difference < 0 ? abs($difference) : 0.0;
        $deposit = $this->calculateDepositDifference($relocation);

        return [
            'old_remaining_value_amount' => $old,
            'new_remaining_value_amount' => $new,
            'price_difference_amount' => $difference,
            'additional_payment_amount' => $this->money($additionalPayment),
            'refund_amount' => $this->money($refund),
            'additional_deposit_amount' => $deposit,
            'cleaning_fee_difference_amount' => 0,
            'service_fee_difference_amount' => $this->money($additionalPayment * 0.05),
            'host_payout_difference_amount' => $this->money($difference),
            'price_difference_payer' => $payer,
            'requires_payment' => $payer === 'guest' && $additionalPayment > 0,
            'payment_status' => $payer === 'guest' && $additionalPayment > 0 ? 'waiting_payment' : 'not_required',
            'requires_refund' => $refund > 0,
            'refund_status' => $refund > 0 ? 'pending' : null,
        ];
    }

    public function remainingNights(BookingRelocation $relocation): int
    {
        $start = CarbonImmutable::parse($relocation->new_period_check_in_date)->startOfDay();
        $end = CarbonImmutable::parse($relocation->new_period_check_out_date)->startOfDay();

        return max(0, (int) $start->diffInDays($end));
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
