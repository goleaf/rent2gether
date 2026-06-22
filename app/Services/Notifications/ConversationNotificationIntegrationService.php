<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\ConversationSystemEvent;
use App\Models\Notification;
use App\Services\Messaging\ConversationService;

class ConversationNotificationIntegrationService
{
    public function __construct(private readonly ConversationService $conversations) {}

    public function addNotificationEventToConversation(Notification $notification): ?ConversationSystemEvent
    {
        $booking = $notification->booking;

        if (! $booking instanceof Booking && $notification->booking_id) {
            $booking = Booking::query()->find($notification->booking_id);
        }

        if (! $booking instanceof Booking) {
            return null;
        }

        $conversation = $this->conversations->getOrCreateForBooking($booking);

        return ConversationSystemEvent::query()->create([
            'conversation_id' => $conversation->id,
            'conversation_message_id' => null,
            'event_key' => $notification->type,
            'event_type' => 'notification',
            'source_type' => 'notification',
            'source_id' => null,
            'booking_id' => $booking->id,
            'property_id' => $notification->property_id,
            'room_id' => $notification->room_id,
            'sleeping_place_id' => $notification->sleeping_place_id,
            'translation_key' => $notification->title_translation_key ?: 'notifications.events.'.$notification->type,
            'translation_params_json' => $notification->translation_params_json ?? [],
            'importance_level' => $notification->priority === 'critical' ? 'critical' : ($notification->is_urgent ? 'urgent' : 'normal'),
            'occurred_at' => now(),
        ]);
    }
}
