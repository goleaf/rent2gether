<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\DisputeEvidence;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DisputeEvidenceService
{
    public function __construct(
        private readonly DisputePrivacyService $privacy,
        private readonly DisputeStatusService $statuses,
        private readonly DisputeEventService $events,
        private readonly DisputeNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadEvidence(User $user, DisputeCase $dispute, array $data): DisputeEvidence
    {
        if (! $this->privacy->canMessage($user, $dispute)) {
            throw ValidationException::withMessages([
                'evidence' => __('disputes.validation.cannot_upload_evidence'),
            ]);
        }

        $evidence = DisputeEvidence::query()->create([
            'dispute_case_id' => $dispute->id,
            'uploaded_by_user_id' => $user->id,
            'evidence_type' => $data['evidence_type'] ?? 'photo',
            'media_type' => $data['media_type'] ?? $data['evidence_type'] ?? null,
            'evidence_role' => $data['evidence_role'] ?? 'other',
            'path' => $data['path'] ?? null,
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'guest_and_host',
        ]);

        $this->statuses->transition($dispute->fresh(), 'evidence_submitted', $user);
        $this->events->record($dispute->fresh(), 'evidence_submitted', ['user_id' => $user->id, 'evidence_id' => $evidence->id]);
        $this->notifications->notifyEvidenceSubmitted($dispute->fresh());

        return $evidence->fresh();
    }

    public function linkEvidence(DisputeCase $dispute, string $sourceType, int $sourceId): DisputeEvidence
    {
        return DisputeEvidence::query()->create([
            'dispute_case_id' => $dispute->id,
            'uploaded_by_user_id' => $dispute->opened_by_user_id,
            'evidence_type' => 'system_event',
            'evidence_role' => 'other',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'visibility' => 'guest_and_host',
        ]);
    }

    /**
     * @return Collection<int, DisputeEvidence>
     */
    public function getVisibleEvidence(User $user, DisputeCase $dispute): Collection
    {
        return $dispute->evidence()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (DisputeEvidence $evidence): bool => $this->privacy->canViewEvidence($user, $evidence))
            ->values();
    }
}
