<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCancellationPolicySnapshot;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class BookingCancellationCalculatorService
{
    public function calculateHoursBeforeCheckIn(Booking $booking): ?int
    {
        $hours = CarbonImmutable::now()->diffInHours($this->checkInAt($booking), false);

        return $hours >= 0 ? (int) floor($hours) : null;
    }

    public function calculateNightsUsed(Booking $booking): int
    {
        $now = CarbonImmutable::now()->startOfDay();
        $checkIn = $this->date($booking->check_in_date ?? $booking->check_in);

        if ($now->lessThanOrEqualTo($checkIn)) {
            return 0;
        }

        return min($this->totalNights($booking), (int) $checkIn->diffInDays($now));
    }

    public function calculateNightsUnused(Booking $booking): int
    {
        return max(0, $this->totalNights($booking) - $this->calculateNightsUsed($booking));
    }

    public function calculateAccommodationRefund(Booking $booking, BookingCancellationPolicySnapshot $snapshot, array $context): float
    {
        return $this->buildRefundBreakdown($booking, [...$context, 'snapshot' => $snapshot])['accommodation_refund_amount'];
    }

    public function calculateCleaningFeeRefund(Booking $booking, array $context): float
    {
        return $this->buildRefundBreakdown($booking, $context)['cleaning_fee_refund_amount'];
    }

    public function calculateServiceFeeRefund(Booking $booking, array $context): float
    {
        return $this->buildRefundBreakdown($booking, $context)['service_fee_refund_amount'];
    }

    public function calculateDepositRefund(Booking $booking, array $context): float
    {
        return $this->buildRefundBreakdown($booking, $context)['deposit_refund_amount'];
    }

    public function calculatePenalty(Booking $booking, BookingCancellationPolicySnapshot $snapshot, array $context): float
    {
        return $this->buildRefundBreakdown($booking, [...$context, 'snapshot' => $snapshot])['penalty_amount'];
    }

    public function calculateHostPayoutAdjustment(Booking $booking, array $context): float
    {
        return $this->buildRefundBreakdown($booking, $context)['host_payout_adjustment_amount'];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function buildRefundBreakdown(Booking $booking, array $context): array
    {
        /** @var BookingCancellationPolicySnapshot|null $snapshot */
        $snapshot = $context['snapshot'] ?? null;
        $cancelledBy = (string) ($context['cancelled_by_type'] ?? $context['requested_by_type'] ?? 'guest');
        $cancellationType = (string) ($context['cancellation_type'] ?? 'guest_fault');
        $currency = strtoupper((string) ($booking->currency ?: 'EUR'));
        $accommodation = $this->money($this->firstMoney($booking->accommodation_amount, $this->firstMoney($booking->subtotal_amount, $booking->subtotal)) - (float) $booking->discount_amount);
        $cleaning = $this->money($this->firstMoney($booking->cleaning_fee_amount, $booking->cleaning_fee));
        $service = $this->money($this->firstMoney($booking->service_fee_amount, $booking->service_fee));
        $deposit = $this->money($this->firstMoney($booking->deposit_amount, $booking->deposit));
        $tax = $this->money($booking->tax_amount);
        $cityFee = $this->money($booking->city_fee_amount);
        $paid = $this->money($this->firstMoney($booking->total_payable, $this->firstMoney($booking->total_amount, $booking->total)));
        $beforeCheckIn = CarbonImmutable::now()->lessThan($this->checkInAt($booking));
        $usedNights = $this->calculateNightsUsed($booking);
        $unusedNights = $this->calculateNightsUnused($booking);

        $accommodationRefund = 0.0;
        $cleaningRefund = 0.0;
        $serviceRefund = 0.0;
        $depositRefund = 0.0;
        $taxRefund = 0.0;
        $cityFeeRefund = 0.0;
        $window = 'late';

        if ($cancelledBy === 'host' || in_array($cancellationType, ['host_fault', 'host_unresponsive_related', 'listing_mismatch_related'], true)) {
            $accommodationRefund = $accommodation;
            $cleaningRefund = $cleaning;
            $serviceRefund = $service;
            $depositRefund = $deposit;
            $taxRefund = $tax;
            $cityFeeRefund = $cityFee;
            $window = 'host_full_refund';
        } elseif ($beforeCheckIn && $snapshot?->free_cancellation_until && CarbonImmutable::now()->lessThanOrEqualTo(CarbonImmutable::instance($snapshot->free_cancellation_until))) {
            $accommodationRefund = $accommodation;
            $cleaningRefund = $cleaning;
            $serviceRefund = $service;
            $depositRefund = $deposit;
            $taxRefund = $tax;
            $cityFeeRefund = $cityFee;
            $window = 'free';
        } elseif ($beforeCheckIn) {
            $depositRefund = $snapshot?->deposit_always_refundable_before_check_in !== false ? $deposit : 0.0;
            $cleaningRefund = $snapshot?->cleaning_fee_refundable_before_check_in !== false ? $cleaning : 0.0;
            $serviceRefund = $snapshot?->service_fee_refundable ? $service : 0.0;
            $accommodationRefund = $this->policyAccommodationRefund($snapshot?->policy_type ?? (string) $booking->cancellation_policy, $accommodation);
            $window = $snapshot?->policy_type === 'non_refundable' ? 'non_refundable' : 'partial';
        } elseif ($cancellationType === 'early_termination' || $usedNights > 0) {
            $nightly = $this->totalNights($booking) > 0 ? $accommodation / $this->totalNights($booking) : 0.0;
            $accommodationRefund = $this->money($nightly * $unusedNights * ($cancellationType === 'housing_problem' ? 1.0 : 0.5));
            $depositRefund = 0.0;
            $window = 'after_check_in';
        }

        $totalRefund = min($paid, $this->money($accommodationRefund + $cleaningRefund + $serviceRefund + $depositRefund + $taxRefund + $cityFeeRefund));
        $nonRefundable = $this->money(max(0.0, $paid - $totalRefund));

        return [
            'hours_before_check_in' => $this->calculateHoursBeforeCheckIn($booking),
            'nights_before_check_in' => $this->calculateNightsBeforeCheckIn($booking),
            'nights_used' => $usedNights,
            'nights_unused' => $unusedNights,
            'accommodation_amount' => $accommodation,
            'cleaning_fee_amount' => $cleaning,
            'service_fee_amount' => $service,
            'deposit_amount' => $deposit,
            'tax_amount' => $tax,
            'city_fee_amount' => $cityFee,
            'accommodation_refund_amount' => $this->money($accommodationRefund),
            'cleaning_fee_refund_amount' => $this->money($cleaningRefund),
            'service_fee_refund_amount' => $this->money($serviceRefund),
            'deposit_refund_amount' => $this->money($depositRefund),
            'tax_refund_amount' => $this->money($taxRefund),
            'city_fee_refund_amount' => $this->money($cityFeeRefund),
            'penalty_amount' => $nonRefundable,
            'host_payout_adjustment_amount' => $this->money(-1 * max(0.0, $accommodationRefund + $cleaningRefund)),
            'total_refund_amount' => $totalRefund,
            'total_non_refundable_amount' => $nonRefundable,
            'currency' => $currency,
            'window' => $window,
            'lines' => $this->lines($currency, $accommodation, $cleaning, $service, $deposit, $tax, $cityFee, $accommodationRefund, $cleaningRefund, $serviceRefund, $depositRefund, $taxRefund, $cityFeeRefund, $nonRefundable),
        ];
    }

    private function calculateNightsBeforeCheckIn(Booking $booking): ?int
    {
        $days = CarbonImmutable::now()->startOfDay()->diffInDays($this->date($booking->check_in_date ?? $booking->check_in), false);

        return $days >= 0 ? (int) floor($days) : null;
    }

    private function policyAccommodationRefund(string $policyType, float $accommodation): float
    {
        return $this->money(match ($policyType) {
            'moderate' => $accommodation * 0.5,
            'strict' => $accommodation * 0.25,
            'non_refundable' => 0,
            default => $accommodation * 0.5,
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lines(string $currency, float $accommodation, float $cleaning, float $service, float $deposit, float $tax, float $cityFee, float $accommodationRefund, float $cleaningRefund, float $serviceRefund, float $depositRefund, float $taxRefund, float $cityFeeRefund, float $penalty): array
    {
        return [
            $this->line('accommodation', $accommodation, $accommodationRefund, $currency, 10),
            $this->line('cleaning_fee', $cleaning, $cleaningRefund, $currency, 20),
            $this->line('service_fee', $service, $serviceRefund, $currency, 30),
            $this->line('deposit', $deposit, $depositRefund, $currency, 40),
            $this->line('tax_future', $tax, $taxRefund, $currency, 50),
            $this->line('city_fee_future', $cityFee, $cityFeeRefund, $currency, 60),
            $this->line('penalty', $penalty, 0.0, $currency, 70, false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function line(string $type, float $amount, float $refund, string $currency, int $sortOrder, bool $refundable = true): array
    {
        return [
            'line_type' => $type,
            'label_key' => 'cancellations.refund_line_types.'.$type,
            'amount' => $this->money($amount),
            'currency' => $currency,
            'refundable' => $refundable && $refund > 0,
            'refund_amount' => $this->money($refund),
            'non_refundable_amount' => $this->money(max(0.0, $amount - $refund)),
            'reason_key' => null,
            'sort_order' => $sortOrder,
        ];
    }

    private function totalNights(Booking $booking): int
    {
        if ((int) $booking->nights_count > 0) {
            return (int) $booking->nights_count;
        }

        return max(0, (int) $this->date($booking->check_in_date ?? $booking->check_in)->diffInDays($this->date($booking->check_out_date ?? $booking->check_out)));
    }

    private function checkInAt(Booking $booking): CarbonImmutable
    {
        $checkIn = $this->date($booking->check_in_date ?? $booking->check_in);

        if ($booking->check_in_time) {
            $time = $booking->check_in_time instanceof CarbonInterface
                ? CarbonImmutable::instance($booking->check_in_time)
                : CarbonImmutable::parse((string) $booking->check_in_time);

            return $checkIn->setTime((int) $time->format('H'), (int) $time->format('i'));
        }

        return $checkIn;
    }

    private function date(CarbonInterface|string|null $date): CarbonImmutable
    {
        if ($date instanceof CarbonInterface) {
            return CarbonImmutable::instance($date)->startOfDay();
        }

        return CarbonImmutable::parse((string) ($date ?: now()->toDateString()))->startOfDay();
    }

    private function firstMoney(mixed $primary, mixed $fallback): float
    {
        return (float) (($primary !== null && (float) $primary !== 0.0) ? $primary : $fallback);
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
