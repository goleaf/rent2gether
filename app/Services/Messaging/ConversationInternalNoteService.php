<?php

namespace App\Services\Messaging;

use App\Models\ConversationInternalNote;
use App\Models\User;
use Illuminate\Support\Collection;

class ConversationInternalNoteService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function addHostNote(User $host, array $context, string $note): ConversationInternalNote
    {
        return ConversationInternalNote::query()->create([
            'conversation_id' => $context['conversation_id'] ?? null,
            'booking_id' => $context['booking_id'] ?? null,
            'booking_stay_id' => $context['booking_stay_id'] ?? null,
            'guest_user_id' => $context['guest_user_id'] ?? null,
            'host_user_id' => $host->id,
            'property_id' => $context['property_id'] ?? null,
            'room_id' => $context['room_id'] ?? null,
            'sleeping_place_id' => $context['sleeping_place_id'] ?? null,
            'note' => $note,
            'note_type' => $context['note_type'] ?? 'other',
            'created_by_user_id' => $host->id,
            'visible_to_host' => true,
            'visible_to_guest' => false,
            'internal' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function getVisibleNotesForHost(User $host, array $context): Collection
    {
        return ConversationInternalNote::query()
            ->where('host_user_id', $host->id)
            ->where('visible_to_host', true)
            ->when($context['conversation_id'] ?? null, fn ($query, int $id) => $query->where('conversation_id', $id))
            ->when($context['booking_id'] ?? null, fn ($query, int $id) => $query->where('booking_id', $id))
            ->latest('id')
            ->get();
    }

    public function deleteHostNote(User $host, ConversationInternalNote $note): void
    {
        abort_unless((int) $note->host_user_id === (int) $host->id, 403);

        $note->delete();
    }
}
