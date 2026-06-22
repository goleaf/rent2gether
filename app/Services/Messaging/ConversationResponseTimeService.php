<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\ConversationEvent;
use App\Models\ConversationMessage;
use App\Models\User;

class ConversationResponseTimeService
{
    public function recordResponseTime(ConversationMessage $message): void
    {
        if ($message->is_system || $message->is_internal_note || ! in_array($message->sender_type, ['guest', 'host'], true)) {
            return;
        }

        $previous = ConversationMessage::query()
            ->where('conversation_id', $message->conversation_id)
            ->where('sender_type', $message->sender_type === 'host' ? 'guest' : 'host')
            ->where('is_system', false)
            ->where('is_internal_note', false)
            ->where('sent_at', '<', $message->sent_at)
            ->latest('sent_at')
            ->first();

        if (! $previous?->sent_at || ! $message->sent_at) {
            return;
        }

        app(ConversationEventService::class)->record($message->conversation, 'response_time_recorded', [
            'user_id' => $message->sender_user_id,
            'message_id' => $message->id,
            'response_minutes' => (int) $previous->sent_at->diffInMinutes($message->sent_at),
            'responder_type' => $message->sender_type,
        ]);
    }

    public function calculateHostAverageResponseTime(User $host): ?int
    {
        return $this->averageFor($host, 'host');
    }

    public function calculateGuestAverageResponseTime(User $guest): ?int
    {
        return $this->averageFor($guest, 'guest');
    }

    /**
     * @return array<string, mixed>
     */
    public function calculateBookingResponseSummary(Booking $booking): array
    {
        return [
            'host_average_minutes' => $booking->host ? $this->calculateHostAverageResponseTime($booking->host) : null,
            'guest_average_minutes' => $booking->guest ? $this->calculateGuestAverageResponseTime($booking->guest) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getHostResponseBadge(User $host): array
    {
        $average = $this->calculateHostAverageResponseTime($host);

        return [
            'average_minutes' => $average,
            'label_key' => $average !== null && $average <= 60
                ? 'messages.messages.fast_response'
                : 'messages.messages.response_time_unknown',
        ];
    }

    private function averageFor(User $user, string $responderType): ?int
    {
        $minutes = ConversationEvent::query()
            ->where('event_key', 'response_time_recorded')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (ConversationEvent $event): ?int => ($event->context_json['responder_type'] ?? null) === $responderType
                ? (int) ($event->context_json['response_minutes'] ?? 0)
                : null)
            ->filter(fn (?int $value): bool => $value !== null && $value > 0);

        if ($minutes->isEmpty()) {
            return null;
        }

        return (int) round($minutes->avg());
    }
}
