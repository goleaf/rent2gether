<?php

namespace App\Services\CheckOut;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\CheckoutRecord;
use App\Models\User;
use App\Services\HostCalendar\HostCalendarSnapshotService;
use App\Services\HostOccupants\HostCurrentStaySnapshotService;
use Illuminate\Validation\ValidationException;

class BookingCheckOutConfirmationService
{
    public function guestConfirm(User $guest, BookingCheckOut $checkOut): BookingCheckOut
    {
        return app(BookingCheckOutService::class)->markGuestCheckedOut($guest, $checkOut);
    }

    public function hostConfirm(User $host, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->ensureHostOwnsCheckOut($host, $checkOut);

        $checkOut->forceFill([
            'host_confirmed_at' => $checkOut->host_confirmed_at ?: now(),
        ])->save();

        app(BookingCheckOutChecklistService::class)->markItemCompleted($host, $checkOut->refresh(), 'host_confirmed');

        if (! $this->canCompleteCheckout($checkOut->refresh())) {
            $status = app(BookingCheckOutIssueService::class)->openIssues($checkOut->refresh())->isNotEmpty()
                ? 'problem_reported'
                : 'host_inspection_pending';

            $checkOut->forceFill(['status' => $status])->save();

            return $checkOut->refresh();
        }

        $this->markBookingCheckedOut($checkOut->refresh());
        $this->updateCurrentOccupants($checkOut->refresh());
        $this->updateHostCalendar($checkOut->refresh());
        app(BookingDepositDecisionService::class)->startReturnIfNoProblems($checkOut->refresh());
        app(BookingReviewRequestService::class)->sendReviewRequests($checkOut->refresh());

        $checkOut->forceFill(['status' => 'completed'])->save();
        app(BookingCheckOutCalendarService::class)->syncCalendarAfterCheckout($checkOut->refresh());
        app(BookingCheckOutChecklistService::class)->markItemCompleted($host, $checkOut->refresh(), 'deposit_reviewed');
        app(BookingCheckOutChecklistService::class)->markItemCompleted($host, $checkOut->refresh(), 'review_requested');

        return $checkOut->refresh();
    }

    public function canCompleteCheckout(BookingCheckOut $checkOut): bool
    {
        if ($checkOut->guest_confirmed_at === null || $checkOut->host_confirmed_at === null) {
            return false;
        }

        if (! $checkOut->room_checked || ! $checkOut->sleeping_place_checked || ! $checkOut->sleeping_place_free) {
            return false;
        }

        if (app(BookingCheckOutIssueService::class)->openIssues($checkOut)->isNotEmpty()) {
            return false;
        }

        $decision = $checkOut->depositDecision;

        return ! $decision || ! in_array($decision->status, ['guest_disputed', 'deduction_requested'], true);
    }

    public function markBookingCheckedOut(BookingCheckOut $checkOut): Booking
    {
        $booking = $checkOut->booking()->firstOrFail();
        $fromStatus = $this->statusValue($booking);
        $now = $checkOut->actual_check_out_at ?: now();

        $booking->forceFill([
            'status' => BookingStatus::CheckedOut,
            'guest_checked_out_at' => $booking->guest_checked_out_at ?: $checkOut->guest_confirmed_at ?: $now,
            'host_confirmed_checkout_at' => $booking->host_confirmed_checkout_at ?: $checkOut->host_confirmed_at ?: $now,
            'checked_out_at' => $booking->checked_out_at ?: $now,
        ])->save();

        if ($fromStatus !== BookingStatus::CheckedOut->value) {
            $booking->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => BookingStatus::CheckedOut->value,
                'changed_by_user_id' => $checkOut->host_user_id,
                'note' => 'check_out.history.checked_out',
            ]);
        }

        CheckoutRecord::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'planned_time' => $checkOut->planned_check_out_time,
                'planned_checkout_time' => $checkOut->planned_check_out_time,
                'actual_departure_at' => $now,
                'actual_checkout_at' => $now,
                'keys_returned' => $checkOut->keys_returned,
                'locker_emptied' => $checkOut->locker_emptied,
                'belongings_collected' => $checkOut->personal_items_taken,
                'belongings_removed' => $checkOut->personal_items_taken,
                'linen_returned' => $checkOut->bedding_returned,
                'place_clean' => ! $checkOut->has_extra_dirty,
                'has_damage' => $checkOut->has_damage,
                'has_extra_dirt' => $checkOut->has_extra_dirty,
                'has_forgotten_items' => $checkOut->has_forgotten_items,
                'deposit_withheld' => $checkOut->needs_deposit_deduction,
                'withhold_amount' => $checkOut->deposit_deduction_amount,
                'withhold_reason' => $checkOut->deposit_deduction_reason,
                'photos_after' => collect([$checkOut->after_place_photo_path, $checkOut->after_room_photo_path])->filter()->values()->all(),
                'damage_media' => $checkOut->damage_photo_paths_json ?? [],
                'guest_confirmed' => $checkOut->guest_confirmed_at !== null,
                'host_confirmed' => $checkOut->host_confirmed_at !== null,
                'guest_confirmed_checkout_at' => $checkOut->guest_confirmed_at,
                'host_confirmed_checkout_at' => $checkOut->host_confirmed_at,
                'status' => $checkOut->has_damage || $checkOut->has_extra_dirty ? 'problem' : 'completed',
            ],
        );

        return $booking->refresh();
    }

    public function updateCurrentOccupants(BookingCheckOut $checkOut): void
    {
        app(HostCurrentStaySnapshotService::class)->refreshForBooking($checkOut->booking()->firstOrFail());
    }

    public function updateHostCalendar(BookingCheckOut $checkOut): void
    {
        app(HostCalendarSnapshotService::class)->refreshForBooking($checkOut->booking()->firstOrFail());
    }

    private function ensureHostOwnsCheckOut(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_host_booking'),
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
