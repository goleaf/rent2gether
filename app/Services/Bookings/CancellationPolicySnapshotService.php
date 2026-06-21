<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCancellationPolicySnapshot;
use App\Models\BookingQuote;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class CancellationPolicySnapshotService
{
    public function __construct(
        private readonly CancellationPolicyService $policies,
    ) {}

    public function createForBooking(Booking $booking): BookingCancellationPolicySnapshot
    {
        $existing = BookingCancellationPolicySnapshot::query()
            ->where('booking_id', $booking->id)
            ->first();

        if ($existing instanceof BookingCancellationPolicySnapshot) {
            return $existing;
        }

        $booking->loadMissing('sleepingPlace');
        $policy = $this->policies->getForSleepingPlace($booking->sleepingPlace);
        $snapshot = $this->buildSnapshotArray($booking);

        $record = BookingCancellationPolicySnapshot::query()->create([
            'booking_id' => $booking->id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'policy_type' => $snapshot['policy_type'],
            'title_snapshot' => $snapshot['title_snapshot'],
            'description_snapshot' => $snapshot['description_snapshot'],
            'rules_snapshot_json' => $snapshot['rules_snapshot_json'],
            'free_cancellation_until' => $snapshot['free_cancellation_until'],
            'cancellation_penalty_starts_at' => $snapshot['cancellation_penalty_starts_at'],
            'first_night_non_refundable' => $policy->first_night_non_refundable,
            'cleaning_fee_refundable_before_check_in' => $policy->cleaning_fee_refundable_before_check_in,
            'service_fee_refundable' => $policy->service_fee_refundable,
            'deposit_always_refundable_before_check_in' => $policy->deposit_always_refundable_before_check_in,
        ]);

        $booking->forceFill([
            'cancellation_policy_snapshot_id' => $record->id,
            'free_cancel_before' => $record->free_cancellation_until,
        ])->save();

        return $record->fresh();
    }

    public function getForBooking(Booking $booking): BookingCancellationPolicySnapshot
    {
        return BookingCancellationPolicySnapshot::query()
            ->where('booking_id', $booking->id)
            ->first()
            ?? $this->createForBooking($booking);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSnapshotArray(Booking $booking): array
    {
        $booking->loadMissing('sleepingPlace');
        $policy = $this->policies->getForSleepingPlace($booking->sleepingPlace);
        $freeUntil = $booking->free_cancel_before ?: $this->deadlineFor($booking, $policy->free_cancellation_until_hours_before_check_in, $policy->free_cancellation_until_days_before_check_in);

        return [
            'policy_type' => $policy->policy_type,
            'title_snapshot' => $policy->title,
            'description_snapshot' => $policy->description,
            'rules_snapshot_json' => [
                'policy_type' => $policy->policy_type,
                'rules' => $policy->rules()
                    ->orderBy('sort_order')
                    ->get(['rule_key', 'applies_when', 'refund_percent', 'fixed_penalty_amount', 'currency'])
                    ->map(fn ($rule): array => $rule->toArray())
                    ->values()
                    ->all(),
            ],
            'free_cancellation_until' => $freeUntil,
            'cancellation_penalty_starts_at' => $freeUntil,
            'first_night_non_refundable' => (bool) $policy->first_night_non_refundable,
            'cleaning_fee_refundable_before_check_in' => (bool) $policy->cleaning_fee_refundable_before_check_in,
            'service_fee_refundable' => (bool) $policy->service_fee_refundable,
            'deposit_always_refundable_before_check_in' => (bool) $policy->deposit_always_refundable_before_check_in,
        ];
    }

    /**
     * Compatibility hook for the existing BookingSnapshotService flow.
     */
    public function createFromQuote(Booking $booking, ?BookingQuote $quote = null): BookingCancellationPolicySnapshot
    {
        return $this->createForBooking($booking);
    }

    private function deadlineFor(Booking $booking, ?int $hours, ?int $days): ?CarbonImmutable
    {
        $hoursBefore = (int) ($hours ?: 0);
        $daysBefore = (int) ($days ?: 0);

        if ($hoursBefore <= 0 && $daysBefore <= 0) {
            return null;
        }

        $checkIn = $this->checkInAt($booking);

        return $daysBefore > 0
            ? $checkIn->subDays($daysBefore)
            : $checkIn->subHours($hoursBefore);
    }

    private function checkInAt(Booking $booking): CarbonImmutable
    {
        $date = $booking->check_in_date ?: $booking->check_in ?: now();
        $checkIn = $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse((string) $date)->startOfDay();

        if ($booking->check_in_time) {
            $time = $booking->check_in_time instanceof CarbonInterface
                ? CarbonImmutable::instance($booking->check_in_time)
                : CarbonImmutable::parse((string) $booking->check_in_time);

            return $checkIn->setTime((int) $time->format('H'), (int) $time->format('i'));
        }

        return $checkIn;
    }
}
