<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\ComplaintCase;
use App\Models\Conversation;
use App\Models\ConversationSystemEvent;

class ConversationSystemEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function addEvent(Conversation $conversation, string $eventKey, array $context = []): ConversationSystemEvent
    {
        $message = app(ConversationMessageService::class)->sendSystemEvent($conversation, $eventKey, $context);

        $event = ConversationSystemEvent::query()->create([
            'conversation_id' => $conversation->id,
            'conversation_message_id' => $message->id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'booking_id' => $context['booking_id'] ?? $conversation->booking_id,
            'property_id' => $conversation->property_id,
            'room_id' => $conversation->room_id,
            'sleeping_place_id' => $conversation->sleeping_place_id,
            'translation_key' => $context['translation_key'] ?? "messages.system_events.{$eventKey}",
            'translation_params_json' => $context['translation_params_json'] ?? [],
            'importance_level' => $context['importance_level'] ?? 'normal',
            'created_by_user_id' => $context['created_by_user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
        ]);

        app(ConversationEventService::class)->record($conversation, 'system_event_added', [
            'event_key' => $eventKey,
            'system_event_id' => $event->id,
        ]);

        return $event;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function addBookingEvent(Booking $booking, string $eventKey, array $context = []): ConversationSystemEvent
    {
        $conversation = app(ConversationService::class)->getOrCreateForBooking($booking);

        return $this->addEvent($conversation, $eventKey, [
            ...$context,
            'source_type' => 'booking',
            'source_id' => $booking->id,
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function addCheckInEvent(BookingCheckIn $checkIn, string $eventKey, array $context = []): ConversationSystemEvent
    {
        $conversation = app(ConversationService::class)->getOrCreateForBooking($checkIn->booking()->firstOrFail());

        return $this->addEvent($conversation, $eventKey, [
            ...$context,
            'source_type' => 'check_in',
            'source_id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function addDepositEvent(mixed $deposit, string $eventKey, array $context = []): ConversationSystemEvent
    {
        $conversation = app(ConversationService::class)->getOrCreateForBooking($deposit->booking()->firstOrFail());

        return $this->addEvent($conversation, $eventKey, [
            ...$context,
            'source_type' => 'deposit',
            'source_id' => $deposit->id,
            'booking_id' => $deposit->booking_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function addComplaintEvent(ComplaintCase $complaint, string $eventKey, array $context = []): ConversationSystemEvent
    {
        $conversation = app(ConversationService::class)->getOrCreateForBooking($complaint->booking()->firstOrFail());

        return $this->addEvent($conversation, $eventKey, [
            ...$context,
            'source_type' => 'complaint',
            'source_id' => $complaint->id,
            'booking_id' => $complaint->booking_id,
        ]);
    }
}
