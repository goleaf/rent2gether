<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ConversationSearchService
{
    public function searchUserConversations(User $user, string $query): Collection
    {
        return $this->baseUserQuery($user)
            ->where(function ($conversationQuery) use ($query): void {
                $conversationQuery->whereHas('conversationMessages', fn ($messageQuery) => $messageQuery
                    ->where('body', 'like', '%'.$query.'%')
                    ->where('is_internal_note', false))
                    ->orWhere('conversation_number', 'like', '%'.$query.'%');
            })
            ->with(['guest:id,name', 'host:id,name', 'lastConversationMessage:id,conversation_id,body,translation_key,sent_at'])
            ->latest('last_message_at')
            ->limit(20)
            ->get();
    }

    public function getRecentForUser(User $user): CursorPaginator
    {
        return $this->baseUserQuery($user)
            ->with(['guest:id,name', 'host:id,name', 'lastConversationMessage:id,conversation_id,body,translation_key,sent_at'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->cursorPaginate(20);
    }

    public function getBookingConversation(Booking $booking): ?Conversation
    {
        return Conversation::query()
            ->where('booking_id', $booking->id)
            ->where('conversation_type', 'booking')
            ->first();
    }

    public function getUrgentForHost(User $host): Collection
    {
        return Conversation::query()
            ->where('host_user_id', $host->id)
            ->where('has_urgent_messages', true)
            ->with(['guest:id,name', 'conversationMessages' => fn ($query) => $query
                ->where('is_urgent', true)
                ->latest('sent_at')
                ->limit(3)])
            ->latest('last_message_at')
            ->limit(10)
            ->get();
    }

    private function baseUserQuery(User $user): Builder
    {
        return Conversation::query()
            ->where(function ($query) use ($user): void {
                $query->where('guest_user_id', $user->id)
                    ->orWhere('host_user_id', $user->id)
                    ->orWhereHas('participants', fn ($participantQuery) => $participantQuery
                        ->where('user_id', $user->id)
                        ->where('can_read', true));
            });
    }
}
