<?php

namespace App\Services\CheckIn;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\User;
use App\Services\Bookings\BookingStatusService;
use App\Services\Stays\BookingStayService;
use Illuminate\Validation\ValidationException;

class BookingCheckInStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingCheckIn $checkIn, string $newStatus, ?User $user = null, array $context = []): BookingCheckIn
    {
        $oldStatus = (string) $checkIn->status;

        if ($oldStatus === $newStatus) {
            return $checkIn;
        }

        if (! $this->canTransition($checkIn, $newStatus)) {
            throw ValidationException::withMessages([
                'status' => __('check_in.validation.invalid_status_transition'),
            ]);
        }

        $checkIn->forceFill([
            ...$this->timestampAttributes($newStatus),
            'status' => $newStatus,
        ])->save();

        $checkIn->statusLogs()->create([
            'booking_id' => $checkIn->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? 'check_in.events.transitioned',
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $checkIn->refresh();
    }

    public function canTransition(BookingCheckIn $checkIn, string $newStatus): bool
    {
        if ((string) $checkIn->status === $newStatus) {
            return true;
        }

        $terminal = ['failed', 'cancelled', 'closed'];

        return ! in_array((string) $checkIn->status, $terminal, true);
    }

    public function syncBookingStatus(BookingCheckIn $checkIn): Booking
    {
        $booking = $checkIn->booking()->firstOrFail();
        $bookingStatus = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
        $statuses = app(BookingStatusService::class);

        if ($checkIn->status === 'host_unresponsive' && $statuses->canTransition($booking, BookingStatus::HostUnresponsive->value)) {
            return $statuses->transition($booking, BookingStatus::HostUnresponsive->value, null, [
                'reason_key' => 'check_in.events.host_unresponsive',
                'event_key' => 'host_unresponsive',
                'event_type' => 'guest_action',
            ]);
        }

        if ($checkIn->status === 'checked_in') {
            if ($bookingStatus === BookingStatus::GuestCheckedIn->value && $statuses->canTransition($booking, BookingStatus::StayInProgress->value)) {
                $booking = $statuses->transition($booking, BookingStatus::StayInProgress->value, null, [
                    'reason_key' => 'check_in.events.checked_in',
                    'event_key' => 'stay_started',
                    'event_type' => 'system',
                ]);

                app(BookingStayService::class)->createFromCheckIn($checkIn->refresh());

                return $booking;
            }

            if (in_array($bookingStatus, [BookingStatus::Confirmed->value, BookingStatus::ReadyForCheckInCore->value], true)
                && $statuses->canTransition($booking, BookingStatus::GuestCheckedIn->value)) {
                $booking = $statuses->transition($booking, BookingStatus::GuestCheckedIn->value, null, [
                    'reason_key' => 'check_in.events.guest_confirmed',
                    'event_key' => 'guest_checked_in',
                    'event_type' => 'guest_action',
                ]);

                $booking = $statuses->transition($booking, BookingStatus::StayInProgress->value, null, [
                    'reason_key' => 'check_in.events.checked_in',
                    'event_key' => 'stay_started',
                    'event_type' => 'system',
                ]);

                app(BookingStayService::class)->createFromCheckIn($checkIn->refresh());

                return $booking;
            }
        }

        if ($checkIn->guest_confirmed_at !== null
            && in_array($bookingStatus, [BookingStatus::Confirmed->value, BookingStatus::ReadyForCheckInCore->value], true)
            && $statuses->canTransition($booking, BookingStatus::GuestCheckedIn->value)) {
            $booking = $statuses->transition($booking, BookingStatus::GuestCheckedIn->value, null, [
                'reason_key' => 'check_in.events.guest_confirmed',
                'event_key' => 'guest_checked_in',
                'event_type' => 'guest_action',
            ]);

            app(BookingStayService::class)->createFromCheckIn($checkIn->refresh());

            return $booking;
        }

        return $booking->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            'instructions_available' => ['instructions_available_at' => now()],
            'guest_on_the_way' => ['guest_on_the_way_at' => now()],
            'guest_arrived' => [
                'guest_arrived_at' => now(),
                'actual_arrival_at' => now(),
                'host_notified_guest_arrived_at' => now(),
            ],
            'guest_confirmed' => ['guest_confirmed_at' => now()],
            'host_confirmed' => ['host_confirmed_at' => now()],
            'checked_in' => [
                'checked_in_at' => now(),
                'actual_check_in_at' => now(),
            ],
            'problem_reported' => [
                'has_problem' => true,
                'problem_reported_at' => now(),
            ],
            'closed' => ['closed_at' => now()],
            default => [],
        };
    }
}
