<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HostUnresponsiveDecisionService
{
    public function __construct(
        private readonly HostUnresponsiveDetectionService $detection,
        private readonly HostUnresponsiveCancellationIntegrationService $cancellations,
        private readonly HostUnresponsiveRelocationIntegrationService $relocations,
        private readonly HostUnresponsiveCheckInIntegrationService $checkIns,
        private readonly HostUnresponsiveNoShowIntegrationService $noShows,
        private readonly HostUnresponsiveComplaintIntegrationService $complaints,
        private readonly HostUnresponsiveRefundIntegrationService $refunds,
        private readonly HostUnresponsiveCalendarIntegrationService $calendar,
        private readonly HostUnresponsiveRatingIntegrationService $ratings,
        private readonly HostUnresponsiveStatusService $statuses,
        private readonly HostUnresponsiveEventService $events,
        private readonly HostUnresponsiveNotificationService $notifications,
    ) {}

    public function confirmHostUnresponsive(BookingHostUnresponsiveCase $case, ?User $actor = null): BookingHostUnresponsiveCase
    {
        if (! $this->detection->canConfirmHostUnresponsive($case)) {
            throw ValidationException::withMessages([
                'host_unresponsive' => __('host_unresponsive.validation.deadline_active'),
            ]);
        }

        return DB::transaction(function () use ($case, $actor): BookingHostUnresponsiveCase {
            $case->forceFill([
                'decision_key' => 'confirmed_host_unresponsive',
                'decision_at' => now(),
                'decided_by_user_id' => $actor?->id,
                'response_deadline_expired_at' => now(),
                'refund_status' => 'review_started',
            ])->save();

            $case = $this->statuses->transition($case->fresh(), 'unresolved', $actor, [
                'reason_key' => 'host_unresponsive.events.deadline_expired',
            ]);

            $this->checkIns->markCheckInFailedIfUnresolved($case);
            $this->noShows->rejectPendingNoShowIfHostUnresponsiveConfirmed($case);
            $this->statuses->syncBookingStatus($case);
            $this->ratings->recordConfirmedHostUnresponsive($case);
            $this->events->record($case, 'deadline_expired');
            $this->events->record($case, 'host_unresponsive_confirmed', ['user_id' => $actor?->id]);
            $this->notifications->notifyGuestDeadlineExpired($case);

            return $case->fresh(['booking', 'checkIn']);
        });
    }

    public function rejectHostUnresponsive(BookingHostUnresponsiveCase $case, string $reason): BookingHostUnresponsiveCase
    {
        $case->forceFill([
            'decision_key' => 'rejected_host_responsive',
            'decision_at' => now(),
            'future_support_comment' => $reason,
        ])->save();

        $case = $this->statuses->transition($case->fresh(), 'resolved', null, [
            'reason_key' => $reason,
        ]);

        $this->ratings->removeRatingImpactIfRejected($case);

        return $case->fresh();
    }

    public function markAccessResolved(BookingHostUnresponsiveCase $case): BookingHostUnresponsiveCase
    {
        $case->forceFill([
            'decision_key' => 'access_resolved',
            'decision_at' => now(),
            'resolved_at' => now(),
        ])->save();

        $case = $this->statuses->transition($case->fresh(), 'access_resolved');
        $this->checkIns->continueCheckInAfterResolution($case);
        $this->events->record($case, 'access_resolved');
        $this->events->record($case, 'check_in_continued');
        $this->notifications->notifyCaseResolved($case);

        return $case->fresh();
    }

    public function convertToCancellation(BookingHostUnresponsiveCase $case): BookingCancellation
    {
        $this->cancellations->applyGuestFriendlyCancellationRules($case);
        $cancellation = $this->cancellations->createCancellation($case->fresh());
        $this->refunds->createRefundIfCancellationConfirmed($case->fresh());
        $this->calendar->releaseLocksIfCancelledBeforeCheckIn($case->fresh());

        $case->forceFill([
            'status' => 'converted_to_cancellation',
            'decision_key' => 'guest_cancelled',
            'booking_cancellation_id' => $cancellation->id,
        ])->save();

        return $cancellation;
    }

    public function convertToRelocation(BookingHostUnresponsiveCase $case, ?SleepingPlace $place = null): mixed
    {
        $relocation = $this->relocations->createRelocationRequest($case, $place);

        $case->forceFill([
            'status' => 'converted_to_relocation',
            'decision_key' => 'guest_relocated',
        ])->save();

        return $relocation;
    }

    public function convertToCheckInProblem(BookingHostUnresponsiveCase $case): mixed
    {
        $case->forceFill([
            'status' => 'converted_to_check_in_problem',
            'decision_key' => 'converted_to_check_in_problem',
            'check_in_problem_id' => $case->check_in_problem_id ?: $case->id,
        ])->save();

        return $case->fresh();
    }

    public function convertToNoShowIfGuestAbsent(BookingHostUnresponsiveCase $case): mixed
    {
        $case->forceFill([
            'status' => 'cancelled',
            'decision_key' => 'converted_to_no_show',
        ])->save();

        return $case->fresh();
    }

    public function openDispute(BookingHostUnresponsiveCase $case, string $reason): mixed
    {
        $case->forceFill([
            'status' => 'dispute_opened',
            'decision_key' => 'dispute_opened',
            'future_support_review_required' => true,
            'future_support_comment' => $reason,
        ])->save();

        $this->statuses->syncBookingStatus($case->fresh());
        $this->events->record($case->fresh(), 'dispute_opened');

        return $case->fresh();
    }

    public function createComplaintIfUnresolved(BookingHostUnresponsiveCase $case): mixed
    {
        return $this->complaints->createComplaintIfUnresolved($case);
    }
}
