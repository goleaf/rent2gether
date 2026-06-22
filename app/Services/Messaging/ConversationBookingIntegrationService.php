<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\Conversation;

class ConversationBookingIntegrationService
{
    public function createConversationAfterBooking(Booking $booking): Conversation
    {
        return app(ConversationService::class)->getOrCreateForBooking($booking);
    }

    public function addBookingCreatedEvent(Booking $booking): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($booking, 'booking_created');
    }

    public function addBookingConfirmedEvent(Booking $booking): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($booking, 'booking_confirmed');
    }

    public function addBookingCancelledEvent(Booking $booking): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($booking, 'booking_cancelled');
    }
}
