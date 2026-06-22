<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\DisputeMessage;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class DisputeMessageService
{
    public function __construct(
        private readonly DisputePrivacyService $privacy,
        private readonly DisputeEventService $events,
    ) {}

    public function sendMessage(User $user, DisputeCase $dispute, string $message): DisputeMessage
    {
        if (! $this->privacy->canMessage($user, $dispute)) {
            throw new AuthorizationException(__('disputes.validation.cannot_message'));
        }

        $record = DisputeMessage::query()->create([
            'dispute_case_id' => $dispute->id,
            'user_id' => $user->id,
            'message_type' => 'statement',
            'message' => $message,
            'visibility' => 'guest_and_host',
        ]);

        $this->events->record($dispute, 'evidence_submitted', ['message_id' => $record->id, 'user_id' => $user->id]);

        return $record->fresh();
    }

    public function sendProposalMessage(User $user, DisputeCase $dispute, string $message): DisputeMessage
    {
        $record = $this->sendMessage($user, $dispute, $message);
        $record->forceFill(['message_type' => 'proposal'])->save();

        return $record->fresh();
    }

    /**
     * @return Collection<int, DisputeMessage>
     */
    public function getVisibleMessages(User $user, DisputeCase $dispute): Collection
    {
        if (! $this->privacy->canMessage($user, $dispute)) {
            return collect();
        }

        return $dispute->messages()->where('visibility', 'guest_and_host')->orderBy('id')->get();
    }
}
