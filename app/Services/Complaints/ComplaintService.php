<?php

namespace App\Services\Complaints;

use App\Enums\BookingStatus;
use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComplaintService
{
    /**
     * @param  array<int, string>  $media
     */
    public function createForBooking(
        Booking $booking,
        User $reporter,
        string $type,
        string $priority,
        string $description,
        ?string $desiredResolution = null,
        bool $refundRequested = false,
        bool $depositHoldRequested = false,
        array $media = [],
    ): Complaint {
        $this->ensureParticipant($booking, $reporter);

        $complaintType = ComplaintType::tryFrom($type);

        if (! $complaintType instanceof ComplaintType || ! $this->typeAllowedForReporter($booking, $reporter, $complaintType)) {
            throw ValidationException::withMessages([
                'type' => __('booking.complaint.errors.type_not_allowed'),
            ]);
        }

        if (! in_array($priority, ['low', 'normal', 'high', 'critical'], true)) {
            throw ValidationException::withMessages([
                'priority' => __('booking.complaint.errors.priority_not_allowed'),
            ]);
        }

        return DB::transaction(function () use ($booking, $reporter, $complaintType, $priority, $description, $desiredResolution, $refundRequested, $depositHoldRequested, $media): Complaint {
            $reportedUserId = $this->reportedUserId($booking, $reporter);
            $initialStatus = $reportedUserId
                ? ComplaintStatus::WaitingForOtherSide
                : ComplaintStatus::Created;

            $complaint = Complaint::query()->create([
                'reporter_user_id' => $reporter->id,
                'reporter_id' => $reporter->id,
                'reported_user_id' => $reportedUserId,
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'bed_id' => $booking->bed_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'type' => $complaintType,
                'priority' => $priority,
                'urgency' => $priority,
                'description' => $description,
                'desired_resolution' => $desiredResolution,
                'refund_requested' => $refundRequested,
                'deposit_hold_requested' => $depositHoldRequested,
                'media' => $media,
                'photos' => $media,
                'status' => $initialStatus,
            ]);

            $this->recordHistory($complaint, ComplaintStatus::Created, $reporter, 'booking.complaint.timeline.created');

            if ($initialStatus === ComplaintStatus::WaitingForOtherSide) {
                $this->recordHistory($complaint, ComplaintStatus::WaitingForOtherSide, null, 'booking.complaint.timeline.waiting_for_other_side');
            }

            Booking::query()->whereKey($booking->id)->update([
                'has_complaint' => true,
                'status' => $this->bookingStatusAfterComplaint($booking),
                'updated_at' => now(),
            ]);

            if ($reportedUserId) {
                $this->refreshReportedUserComplaintCount($reportedUserId);
            }

            return $complaint->refresh();
        });
    }

    public function respondAsOtherSide(Complaint $complaint, User $responder, string $response): Complaint
    {
        $reportedUserId = $complaint->reported_user_id;

        if (! $reportedUserId || (int) $reportedUserId !== (int) $responder->id) {
            throw ValidationException::withMessages([
                'otherSideResponse' => __('booking.complaint.errors.response_not_allowed'),
            ]);
        }

        if ($complaint->other_side_response || $complaint->respondent_reply) {
            throw ValidationException::withMessages([
                'otherSideResponse' => __('booking.complaint.errors.response_already_saved'),
            ]);
        }

        if (in_array($complaint->status, [ComplaintStatus::Resolved, ComplaintStatus::Closed, ComplaintStatus::Cancelled, ComplaintStatus::Dismissed], true)) {
            throw ValidationException::withMessages([
                'otherSideResponse' => __('booking.complaint.errors.response_not_allowed'),
            ]);
        }

        return DB::transaction(function () use ($complaint, $responder, $response): Complaint {
            $complaint->forceFill([
                'other_side_response' => $response,
                'respondent_reply' => $response,
                'status' => ComplaintStatus::UnderReviewBySystem,
            ])->save();

            $this->recordHistory($complaint, ComplaintStatus::UnderReviewBySystem, $responder, 'booking.complaint.timeline.other_side_responded');

            return $complaint->refresh();
        });
    }

    public function canView(Complaint $complaint, User $user): bool
    {
        return in_array((int) $user->id, [
            (int) $complaint->reporter_user_id,
            (int) $complaint->reporter_id,
            (int) $complaint->reported_user_id,
        ], true);
    }

    public function canRespond(Complaint $complaint, User $user): bool
    {
        return (int) $complaint->reported_user_id === (int) $user->id
            && ! $complaint->other_side_response
            && ! $complaint->respondent_reply
            && ! in_array($complaint->status, [ComplaintStatus::Resolved, ComplaintStatus::Closed, ComplaintStatus::Cancelled, ComplaintStatus::Dismissed], true);
    }

    private function ensureParticipant(Booking $booking, User $reporter): void
    {
        if (! in_array((int) $reporter->id, [
            (int) $booking->guest_user_id,
            (int) $booking->guest_id,
            (int) $booking->host_user_id,
            (int) $booking->host_id,
        ], true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.complaint.errors.not_your_booking'),
            ]);
        }
    }

    private function typeAllowedForReporter(Booking $booking, User $reporter, ComplaintType $type): bool
    {
        $allowed = $this->isHostReporter($booking, $reporter)
            ? ComplaintType::hostTypes()
            : ComplaintType::guestTypes();

        return in_array($type, $allowed, true);
    }

    private function reportedUserId(Booking $booking, User $reporter): ?int
    {
        if ($this->isHostReporter($booking, $reporter)) {
            return $booking->guest_user_id ?: $booking->guest_id;
        }

        return $booking->host_user_id ?: $booking->host_id;
    }

    private function isHostReporter(Booking $booking, User $reporter): bool
    {
        return in_array((int) $reporter->id, [
            (int) $booking->host_user_id,
            (int) $booking->host_id,
        ], true);
    }

    private function bookingStatusAfterComplaint(Booking $booking): BookingStatus
    {
        if (in_array($booking->status, [
            BookingStatus::Confirmed,
            BookingStatus::CheckedIn,
            BookingStatus::InProgress,
            BookingStatus::ActiveStay,
            BookingStatus::CheckedOut,
            BookingStatus::Completed,
        ], true)) {
            return BookingStatus::ProblemReported;
        }

        return $booking->status;
    }

    private function recordHistory(Complaint $complaint, ComplaintStatus $status, ?User $actor, string $noteKey): void
    {
        $complaint->statusHistories()->create([
            'actor_user_id' => $actor?->id,
            'status' => $status,
            'note_key' => $noteKey,
            'metadata_json' => [],
        ]);
    }

    private function refreshReportedUserComplaintCount(int $userId): void
    {
        $count = Complaint::query()
            ->where('reported_user_id', $userId)
            ->count();

        User::query()
            ->whereKey($userId)
            ->update(['complaints_count' => $count]);

        UserProfile::query()
            ->where('user_id', $userId)
            ->update(['complaints_count' => $count]);
    }
}
