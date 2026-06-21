<?php

namespace App\Services\CheckIn;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\CheckinRecord;
use App\Models\User;
use App\Services\HostCalendar\HostCalendarSnapshotService;
use App\Services\HostOccupants\HostCurrentStaySnapshotService;
use App\Services\Stays\BookingStayService;
use Illuminate\Validation\ValidationException;

class BookingCheckInConfirmationService
{
    public function guestConfirm(User $guest, BookingCheckIn $checkIn): BookingCheckIn
    {
        $this->ensureGuestOwnsCheckIn($guest, $checkIn);

        $checkIn->forceFill([
            'guest_confirmed_at' => $checkIn->guest_confirmed_at ?: now(),
            'status' => $checkIn->host_confirmed_at ? $checkIn->status : 'guest_confirmed',
        ])->save();

        app(BookingCheckInChecklistService::class)->markItemCompleted($guest, $checkIn->refresh(), 'guest_confirmed');

        return $this->startStayIfReady($checkIn->refresh());
    }

    public function hostConfirm(User $host, BookingCheckIn $checkIn): BookingCheckIn
    {
        $this->ensureHostOwnsCheckIn($host, $checkIn);

        $checkIn->forceFill([
            'host_confirmed_at' => $checkIn->host_confirmed_at ?: now(),
            'status' => $checkIn->guest_confirmed_at ? $checkIn->status : 'host_confirmed',
        ])->save();

        app(BookingCheckInChecklistService::class)->markItemCompleted($host, $checkIn->refresh(), 'host_confirmed');

        return $this->startStayIfReady($checkIn->refresh());
    }

    public function canMarkCheckedIn(BookingCheckIn $checkIn): bool
    {
        if (app(BookingCheckInProblemService::class)->openSevereProblems($checkIn)->isNotEmpty()) {
            return false;
        }

        $selfCheckInReady = $checkIn->check_in_method === 'self_check_in'
            && $checkIn->guest_confirmed_at !== null;

        return $selfCheckInReady
            || ($checkIn->guest_confirmed_at !== null && $checkIn->host_confirmed_at !== null);
    }

    public function startStayIfReady(BookingCheckIn $checkIn): BookingCheckIn
    {
        if (! $this->canMarkCheckedIn($checkIn)) {
            $status = app(BookingCheckInProblemService::class)->openSevereProblems($checkIn)->isNotEmpty()
                ? 'waiting_for_resolution'
                : $this->pendingStatus($checkIn);

            $checkIn->forceFill(['status' => $status])->save();

            return $checkIn->refresh();
        }

        $checkIn->forceFill([
            'status' => 'checked_in',
            'actual_check_in_at' => $checkIn->actual_check_in_at ?: now(),
        ])->save();

        $this->markBookingCheckedIn($checkIn->refresh());
        app(BookingStayService::class)->createFromCheckIn($checkIn->refresh());
        $this->updateCurrentOccupants($checkIn->refresh());
        $this->updateHostCalendar($checkIn->refresh());

        return $checkIn->refresh();
    }

    public function markBookingCheckedIn(BookingCheckIn $checkIn): Booking
    {
        $booking = $checkIn->booking()->firstOrFail();
        $fromStatus = $this->statusValue($booking);
        $now = $checkIn->actual_check_in_at ?: now();

        $booking->forceFill([
            'status' => BookingStatus::InProgress,
            'guest_checked_in_at' => $booking->guest_checked_in_at ?: $checkIn->guest_confirmed_at ?: $now,
            'host_confirmed_checkin_at' => $booking->host_confirmed_checkin_at ?: $checkIn->host_confirmed_at,
            'checked_in_at' => $booking->checked_in_at ?: $now,
        ])->save();

        if ($fromStatus !== BookingStatus::InProgress->value) {
            $booking->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => BookingStatus::InProgress->value,
                'changed_by_user_id' => $checkIn->host_confirmed_at ? $checkIn->host_user_id : $checkIn->guest_user_id,
                'note' => 'check_in.history.checked_in',
            ]);
        }

        CheckinRecord::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'planned_time' => $checkIn->planned_check_in_time,
                'actual_arrival_at' => $checkIn->actual_arrival_at ?: $now,
                'met_by' => $checkIn->met_by_name,
                'keys_handed' => $checkIn->keys_handed_over,
                'keys_received' => $checkIn->keys_handed_over,
                'code_received' => $checkIn->door_code_shared || $checkIn->intercom_code_shared || $checkIn->key_safe_code_shared,
                'room_shown' => $checkIn->room_shown,
                'sleeping_place_shown' => $checkIn->sleeping_place_shown,
                'rules_explained' => $checkIn->rules_explained,
                'linen_provided' => $checkIn->bedding_given,
                'towel_provided' => $checkIn->towel_given,
                'locker_assigned' => $checkIn->locker_given,
                'guest_confirmed' => $checkIn->guest_confirmed_at !== null,
                'host_confirmed' => $checkIn->host_confirmed_at !== null,
                'guest_confirmed_at' => $checkIn->guest_confirmed_at,
                'host_confirmed_at' => $checkIn->host_confirmed_at,
                'has_issue' => $checkIn->has_problem,
                'status' => $checkIn->has_problem ? 'problem' : 'completed',
            ],
        );

        return $booking->refresh();
    }

    public function updateCurrentOccupants(BookingCheckIn $checkIn): void
    {
        app(HostCurrentStaySnapshotService::class)->refreshForBooking($checkIn->booking()->firstOrFail());
    }

    public function updateHostCalendar(BookingCheckIn $checkIn): void
    {
        app(HostCalendarSnapshotService::class)->refreshForBooking($checkIn->booking()->firstOrFail());
    }

    private function pendingStatus(BookingCheckIn $checkIn): string
    {
        if ($checkIn->guest_confirmed_at !== null && $checkIn->host_confirmed_at === null) {
            return 'guest_confirmed';
        }

        if ($checkIn->host_confirmed_at !== null && $checkIn->guest_confirmed_at === null) {
            return 'host_confirmed';
        }

        return 'waiting_for_arrival';
    }

    private function ensureGuestOwnsCheckIn(User $guest, BookingCheckIn $checkIn): void
    {
        if ((int) $checkIn->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_your_booking'),
            ]);
        }
    }

    private function ensureHostOwnsCheckIn(User $host, BookingCheckIn $checkIn): void
    {
        if ((int) $checkIn->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_host_booking'),
            ]);
        }
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
