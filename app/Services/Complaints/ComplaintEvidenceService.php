<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\ComplaintEvidence;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ComplaintEvidenceService
{
    public function __construct(
        private readonly ComplaintPrivacyService $privacy,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadEvidence(User $user, ComplaintCase $case, array $data): ComplaintEvidence
    {
        if (! $this->privacy->canRespond($user, $case)) {
            throw ValidationException::withMessages([
                'evidence' => __('complaints.validation.cannot_upload_evidence'),
            ]);
        }

        $evidence = ComplaintEvidence::query()->create([
            'complaint_case_id' => $case->id,
            'booking_id' => $case->booking_id,
            'uploaded_by_user_id' => $user->id,
            'evidence_type' => $data['evidence_type'] ?? 'photo',
            'media_type' => $data['media_type'] ?? $data['evidence_type'] ?? null,
            'evidence_role' => $data['evidence_role'] ?? 'other',
            'path' => $data['path'] ?? null,
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'message_thread_id' => $data['message_thread_id'] ?? null,
            'message_id' => $data['message_id'] ?? null,
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'guest_and_host',
        ]);

        $this->statuses->transition($case->fresh(), 'evidence_submitted', $user);
        $this->events->record($case->fresh(), 'evidence_submitted', ['user_id' => $user->id, 'evidence_id' => $evidence->id]);

        return $evidence->fresh();
    }

    public function linkExistingEvidence(ComplaintCase $case, string $sourceType, int $sourceId): ComplaintEvidence
    {
        $evidence = ComplaintEvidence::query()->create([
            'complaint_case_id' => $case->id,
            'booking_id' => $case->booking_id,
            'uploaded_by_user_id' => $case->reporter_user_id,
            'evidence_type' => 'system_event',
            'evidence_role' => 'other',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'visibility' => 'guest_and_host',
        ]);

        $this->events->record($case, 'evidence_submitted', ['source_type' => $sourceType, 'source_id' => $sourceId]);

        return $evidence->fresh();
    }

    /**
     * @return Collection<int, ComplaintEvidence>
     */
    public function getVisibleEvidence(User $user, ComplaintCase $case): Collection
    {
        return $case->evidence()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (ComplaintEvidence $evidence): bool => $this->privacy->canViewEvidence($user, $evidence))
            ->values();
    }

    public function requestMoreEvidence(ComplaintCase $case, User $requester, string $message): void
    {
        $this->statuses->transition($case, 'evidence_requested', $requester, ['note' => $message]);
        $this->events->record($case->fresh(), 'evidence_requested', ['user_id' => $requester->id, 'message' => $message]);
    }
}
