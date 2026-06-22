<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;

class ConversationParticipantService
{
    public function addGuest(Conversation $conversation, User $guest): ConversationParticipant
    {
        return $this->addUser($conversation, $guest, 'guest');
    }

    public function addHost(Conversation $conversation, User $host): ConversationParticipant
    {
        return $this->addUser($conversation, $host, 'host');
    }

    public function addHostRepresentative(Conversation $conversation, mixed $representative): ConversationParticipant
    {
        $user = $representative instanceof User ? $representative : null;

        return ConversationParticipant::query()->updateOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $user?->id,
            'participant_type' => 'host_representative',
        ], [
            'display_name_snapshot' => $user?->name ?? (string) ($representative->name ?? $representative->display_name ?? __('messages.participant_types.host_representative')),
            'can_write' => true,
            'can_read' => true,
            'can_upload' => true,
            'can_use_templates' => true,
            'joined_at' => now(),
        ]);
    }

    public function addSystem(Conversation $conversation): ConversationParticipant
    {
        return ConversationParticipant::query()->updateOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'participant_type' => 'system',
        ], [
            'display_name_snapshot' => __('messages.participant_types.system'),
            'can_write' => false,
            'can_read' => true,
            'can_upload' => false,
            'can_use_templates' => false,
            'joined_at' => now(),
        ]);
    }

    public function removeParticipant(ConversationParticipant $participant): void
    {
        $participant->update([
            'can_write' => false,
            'can_read' => false,
            'left_at' => now(),
        ]);
    }

    public function mute(User $user, Conversation $conversation): void
    {
        $this->participantFor($user, $conversation)?->update(['muted' => true]);
    }

    public function unmute(User $user, Conversation $conversation): void
    {
        $this->participantFor($user, $conversation)?->update(['muted' => false]);
    }

    private function addUser(Conversation $conversation, User $user, string $type): ConversationParticipant
    {
        return ConversationParticipant::query()->updateOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'participant_type' => $type,
        ], [
            'display_name_snapshot' => $user->name,
            'can_write' => true,
            'can_read' => true,
            'can_upload' => true,
            'can_use_templates' => true,
            'joined_at' => now(),
        ]);
    }

    private function participantFor(User $user, Conversation $conversation): ?ConversationParticipant
    {
        return $conversation->participants()
            ->where('user_id', $user->id)
            ->first();
    }
}
