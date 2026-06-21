<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingNoShowPolicySnapshot;

class BookingNoShowPolicySnapshotService
{
    public function __construct(
        private readonly BookingNoShowPolicyService $policies,
    ) {}

    public function createForBooking(Booking $booking): BookingNoShowPolicySnapshot
    {
        $existing = BookingNoShowPolicySnapshot::query()
            ->where('booking_id', $booking->id)
            ->first();

        if ($existing instanceof BookingNoShowPolicySnapshot) {
            return $existing;
        }

        $snapshot = $this->buildSnapshotArray($booking);

        return BookingNoShowPolicySnapshot::query()->create([
            'booking_id' => $booking->id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'waiting_period_minutes' => $snapshot['waiting_period_minutes'],
            'same_day_waiting_period_minutes' => $snapshot['same_day_waiting_period_minutes'],
            'night_arrival_waiting_period_minutes' => $snapshot['night_arrival_waiting_period_minutes'],
            'hold_first_night_on_no_show' => $snapshot['hold_first_night_on_no_show'],
            'release_remaining_nights_after_no_show' => $snapshot['release_remaining_nights_after_no_show'],
            'refund_deposit_on_no_show' => $snapshot['refund_deposit_on_no_show'],
            'refund_cleaning_fee_on_no_show' => $snapshot['refund_cleaning_fee_on_no_show'],
            'refund_service_fee_on_no_show' => $snapshot['refund_service_fee_on_no_show'],
            'host_payout_rule' => $snapshot['host_payout_rule'],
            'guest_penalty_rule' => $snapshot['guest_penalty_rule'],
            'policy_snapshot_json' => $snapshot,
        ])->fresh();
    }

    public function getForBooking(Booking $booking): BookingNoShowPolicySnapshot
    {
        return BookingNoShowPolicySnapshot::query()
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

        return [
            'policy_id' => $policy->id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'waiting_period_minutes' => $policy->waiting_period_minutes,
            'same_day_waiting_period_minutes' => $policy->same_day_waiting_period_minutes,
            'night_arrival_waiting_period_minutes' => $policy->night_arrival_waiting_period_minutes,
            'hold_first_night_on_no_show' => (bool) $policy->hold_first_night_on_no_show,
            'release_remaining_nights_after_no_show' => (bool) $policy->release_remaining_nights_after_no_show,
            'refund_deposit_on_no_show' => (bool) $policy->refund_deposit_on_no_show,
            'refund_cleaning_fee_on_no_show' => (bool) $policy->refund_cleaning_fee_on_no_show,
            'refund_service_fee_on_no_show' => (bool) $policy->refund_service_fee_on_no_show,
            'host_payout_rule' => $policy->host_payout_rule,
            'guest_penalty_rule' => $policy->guest_penalty_rule,
        ];
    }
}
