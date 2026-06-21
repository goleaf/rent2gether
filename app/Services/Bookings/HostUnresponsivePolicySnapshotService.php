<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\HostUnresponsivePolicySnapshot;

class HostUnresponsivePolicySnapshotService
{
    public function __construct(
        private readonly HostUnresponsivePolicyService $policies,
    ) {}

    public function createForBooking(Booking $booking): HostUnresponsivePolicySnapshot
    {
        $existing = HostUnresponsivePolicySnapshot::query()
            ->where('booking_id', $booking->id)
            ->first();

        if ($existing instanceof HostUnresponsivePolicySnapshot) {
            return $existing;
        }

        $snapshot = $this->buildSnapshotArray($booking);

        return HostUnresponsivePolicySnapshot::query()->create([
            'booking_id' => $booking->id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'property_id' => $booking->property_id,
            'pre_check_in_response_minutes' => $snapshot['pre_check_in_response_minutes'],
            'check_in_response_minutes' => $snapshot['check_in_response_minutes'],
            'guest_waiting_outside_response_minutes' => $snapshot['guest_waiting_outside_response_minutes'],
            'night_entry_response_minutes' => $snapshot['night_entry_response_minutes'],
            'urgent_response_minutes' => $snapshot['urgent_response_minutes'],
            'notify_representative_if_available' => $snapshot['notify_representative_if_available'],
            'auto_show_instructions_if_allowed' => $snapshot['auto_show_instructions_if_allowed'],
            'auto_block_no_show_while_active' => $snapshot['auto_block_no_show_while_active'],
            'allow_guest_cancellation_after_deadline' => $snapshot['allow_guest_cancellation_after_deadline'],
            'allow_guest_relocation_after_deadline' => $snapshot['allow_guest_relocation_after_deadline'],
            'guest_friendly_refund_if_confirmed' => $snapshot['guest_friendly_refund_if_confirmed'],
            'policy_snapshot_json' => $snapshot,
        ])->fresh();
    }

    public function getForBooking(Booking $booking): HostUnresponsivePolicySnapshot
    {
        return HostUnresponsivePolicySnapshot::query()
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
        $policy = $this->policies->getForBooking($booking);

        return [
            'policy_id' => $policy->id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'property_id' => $booking->property_id,
            'pre_check_in_response_minutes' => $policy->pre_check_in_response_minutes,
            'check_in_response_minutes' => $policy->check_in_response_minutes,
            'guest_waiting_outside_response_minutes' => $policy->guest_waiting_outside_response_minutes,
            'night_entry_response_minutes' => $policy->night_entry_response_minutes,
            'urgent_response_minutes' => $policy->urgent_response_minutes,
            'notify_representative_if_available' => (bool) $policy->notify_representative_if_available,
            'auto_show_instructions_if_allowed' => (bool) $policy->auto_show_instructions_if_allowed,
            'auto_block_no_show_while_active' => (bool) $policy->auto_block_no_show_while_active,
            'allow_guest_cancellation_after_deadline' => (bool) $policy->allow_guest_cancellation_after_deadline,
            'allow_guest_relocation_after_deadline' => (bool) $policy->allow_guest_relocation_after_deadline,
            'guest_friendly_refund_if_confirmed' => (bool) $policy->guest_friendly_refund_if_confirmed,
        ];
    }
}
