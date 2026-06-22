<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\DisputeEvidence;
use App\Models\User;

class DisputePrivacyService
{
    public function canGuestView(User $guest, DisputeCase $dispute): bool
    {
        return (int) $dispute->guest_user_id === (int) $guest->id || (int) $dispute->opened_by_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, DisputeCase $dispute): bool
    {
        return (int) $dispute->host_user_id === (int) $host->id || (int) $dispute->opened_by_user_id === (int) $host->id;
    }

    public function canMessage(User $user, DisputeCase $dispute): bool
    {
        return $this->canGuestView($user, $dispute) || $this->canHostView($user, $dispute);
    }

    public function canViewEvidence(User $user, DisputeEvidence $evidence): bool
    {
        $evidence->loadMissing('disputeCase');

        return match ($evidence->visibility) {
            'internal', 'future_review_only' => false,
            'guest_only' => (int) $evidence->disputeCase?->guest_user_id === (int) $user->id,
            'host_only' => (int) $evidence->disputeCase?->host_user_id === (int) $user->id,
            default => $evidence->disputeCase instanceof DisputeCase && $this->canMessage($user, $evidence->disputeCase),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, DisputeCase $dispute): array
    {
        $data = $dispute->toArray();
        unset($data['future_decision_required'], $data['future_decision_comment']);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, DisputeCase $dispute): array
    {
        $data = $dispute->toArray();
        unset($data['future_decision_required'], $data['future_decision_comment']);

        return $data;
    }
}
