<?php

namespace App\Services\Complaints;

use App\Models\Booking;
use App\Models\ComplaintCase;
use App\Models\ComplaintEvidence;
use App\Models\User;

class ComplaintPrivacyService
{
    public function canGuestView(User $guest, ComplaintCase $case): bool
    {
        if ((int) $case->guest_user_id === (int) $guest->id || (int) $case->reporter_user_id === (int) $guest->id || (int) $case->against_user_id === (int) $guest->id) {
            return true;
        }

        return $case->parties()->where('user_id', $guest->id)->exists();
    }

    public function canHostView(User $host, ComplaintCase $case): bool
    {
        return (int) $case->host_user_id === (int) $host->id
            || (int) $case->reporter_user_id === (int) $host->id
            || (int) $case->against_user_id === (int) $host->id;
    }

    public function canRespond(User $user, ComplaintCase $case): bool
    {
        return (int) $case->guest_user_id === (int) $user->id
            || (int) $case->host_user_id === (int) $user->id
            || (int) $case->reporter_user_id === (int) $user->id
            || (int) $case->against_user_id === (int) $user->id
            || $case->parties()->where('user_id', $user->id)->where('can_respond', true)->exists();
    }

    public function canViewEvidence(User $user, ComplaintEvidence $evidence): bool
    {
        $evidence->loadMissing('complaintCase');

        return match ($evidence->visibility) {
            'internal', 'future_review_only' => false,
            'reporter_only' => (int) $evidence->complaintCase?->reporter_user_id === (int) $user->id,
            'against_only' => (int) $evidence->complaintCase?->against_user_id === (int) $user->id,
            'host_only' => (int) $evidence->complaintCase?->host_user_id === (int) $user->id,
            'guest_only' => (int) $evidence->complaintCase?->guest_user_id === (int) $user->id,
            default => $evidence->complaintCase instanceof ComplaintCase && $this->canRespond($user, $evidence->complaintCase),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, ComplaintCase $case): array
    {
        $data = $case->toArray();
        unset($data['future_review_required'], $data['future_review_comment']);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, ComplaintCase $case): array
    {
        $data = $case->toArray();
        unset($data['future_review_required'], $data['future_review_comment']);

        return $data;
    }

    public function canGuestCreate(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id || (int) $booking->guest_id === (int) $guest->id;
    }

    public function canHostCreate(User $host, Booking $booking): bool
    {
        return (int) $booking->host_user_id === (int) $host->id || (int) $booking->host_id === (int) $host->id;
    }
}
