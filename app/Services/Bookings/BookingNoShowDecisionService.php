<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingNoShow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingNoShowDecisionService
{
    public function __construct(
        private readonly BookingNoShowDetectionService $detection,
        private readonly BookingNoShowCalculatorService $calculator,
        private readonly BookingNoShowCancellationIntegrationService $cancellations,
        private readonly BookingNoShowRefundIntegrationService $refunds,
        private readonly BookingNoShowCalendarService $calendar,
        private readonly BookingNoShowCheckInIntegrationService $checkIns,
        private readonly BookingNoShowStayIntegrationService $stays,
        private readonly BookingNoShowRatingIntegrationService $ratings,
        private readonly BookingNoShowStatusService $statuses,
        private readonly BookingNoShowEventService $events,
        private readonly BookingNoShowNotificationService $notifications,
    ) {}

    public function confirmNoShow(User $actor, BookingNoShow $noShow): BookingNoShow
    {
        if (! $this->detection->canConfirmNoShow($noShow)) {
            throw ValidationException::withMessages([
                'no_show' => __('no_show.validation.waiting_period_active'),
            ]);
        }

        return DB::transaction(function () use ($actor, $noShow): BookingNoShow {
            $amounts = $this->calculator->calculateRefundAndPenalty($noShow);

            $noShow->forceFill([
                ...$amounts,
                'decision_key' => 'confirmed_no_show',
                'decided_by_user_id' => $actor->id,
                'decision_at' => now(),
                'waiting_expired_at' => $noShow->waiting_until && $noShow->waiting_until->isPast() ? now() : $noShow->waiting_expired_at,
                'refund_or_penalty_status' => 'calculated',
            ])->save();

            $cancellation = $this->cancellations->createCancellationFromNoShow($noShow->fresh());
            $noShow->forceFill(['booking_cancellation_id' => $cancellation->id])->save();
            $refund = $this->refunds->createRefundIfNeeded($noShow->fresh());
            $noShow = $this->statuses->transition($noShow->fresh(), 'confirmed_no_show', $actor, [
                'reason_key' => 'no_show.events.no_show_confirmed',
            ]);

            if ($refund) {
                $noShow->forceFill(['booking_refund_id' => $refund->id])->save();
            }

            $this->checkIns->markCheckInFailedDueToNoShow($noShow->fresh());
            $this->stays->ensureNoStayCreated($noShow->fresh());
            $this->stays->recalculateOccupancy($noShow->fresh());
            $this->calendar->releaseDatesAfterNoShow($noShow->fresh());
            $this->ratings->recordConfirmedNoShow($noShow->fresh());
            $this->statuses->syncBookingStatus($noShow->fresh());
            $this->events->record($noShow->fresh(), 'no_show_confirmed', ['user_id' => $actor->id]);
            $this->events->record($noShow->fresh(), 'booking_marked_no_show');
            $this->notifications->notifyHostNoShowConfirmed($noShow->fresh());
            $this->notifications->notifyGuestNoShowConfirmed($noShow->fresh());

            return $noShow->fresh(['checkIn', 'bookingCancellation', 'bookingRefund']);
        });
    }

    public function rejectNoShow(User $actor, BookingNoShow $noShow, string $reason): BookingNoShow
    {
        $noShow->forceFill([
            'decision_key' => 'rejected_guest_arrived',
            'decided_by_user_id' => $actor->id,
            'decision_at' => now(),
            'guest_comment' => $reason,
        ])->save();

        $noShow = $this->statuses->transition($noShow->fresh(), 'rejected_no_show', $actor, [
            'reason_key' => $reason,
        ]);

        $this->ratings->removeNoShowRatingImpactIfRejected($noShow);
        $this->notifications->notifyGuestNoShowRejected($noShow);

        return $noShow->fresh();
    }

    public function convertToCancellation(BookingNoShow $noShow): BookingCancellation
    {
        $cancellation = $this->cancellations->createCancellationFromNoShow($noShow);
        $noShow->forceFill([
            'status' => 'converted_to_cancellation',
            'decision_key' => 'converted_to_cancellation',
            'booking_cancellation_id' => $cancellation->id,
        ])->save();

        return $cancellation;
    }

    public function convertToHostUnresponsive(BookingNoShow $noShow): mixed
    {
        $noShow->forceFill([
            'status' => 'converted_to_host_unresponsive',
            'decision_key' => 'converted_to_host_unresponsive',
            'host_unresponsive_case_id' => $noShow->id,
            'future_support_review_required' => true,
        ])->save();
        $this->statuses->syncBookingStatus($noShow->fresh());

        return $noShow->fresh();
    }

    public function convertToCheckInProblem(BookingNoShow $noShow): mixed
    {
        $noShow->forceFill([
            'status' => 'rejected_no_show',
            'decision_key' => 'converted_to_check_in_problem',
        ])->save();

        return $noShow->fresh();
    }

    public function openDispute(BookingNoShow $noShow, string $reason): mixed
    {
        $noShow->forceFill([
            'status' => 'dispute_opened',
            'decision_key' => 'dispute_opened',
            'future_support_review_required' => true,
            'future_support_comment' => $reason,
        ])->save();
        $this->statuses->syncBookingStatus($noShow->fresh());

        return $noShow->fresh();
    }
}
