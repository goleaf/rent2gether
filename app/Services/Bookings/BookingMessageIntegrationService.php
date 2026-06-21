<?php

namespace App\Services\Bookings;

use App\Enums\MessageThreadType;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\MessageThread;

class BookingMessageIntegrationService
{
    public function createOrLinkBookingThread(Booking $booking): MessageThread
    {
        return MessageThread::query()->firstOrCreate(
            [
                'booking_id' => $booking->id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
            ],
            [
                'type' => MessageThreadType::Booking->value,
                'property_id' => $booking->property_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'last_message_at' => now(),
                'status' => 'active',
            ],
        );
    }

    public function sendBookingCreatedSystemMessage(Booking $booking): void
    {
        $this->sendSystemMessage($booking, 'bookings.messages.created');
    }

    public function sendBookingConfirmedSystemMessage(Booking $booking): void
    {
        $this->sendSystemMessage($booking, 'bookings.messages.confirmed');
    }

    public function sendCheckInReadySystemMessage(Booking $booking): void
    {
        $this->sendSystemMessage($booking, 'bookings.messages.ready_for_check_in');
    }

    private function sendSystemMessage(Booking $booking, string $bodyKey): void
    {
        $thread = $this->createOrLinkBookingThread($booking);
        $conversation = Conversation::query()->firstOrCreate(
            [
                'booking_id' => $booking->id,
                'participant_one_id' => $booking->guest_user_id,
                'participant_two_id' => $booking->host_user_id,
            ],
            [
                'bed_id' => $booking->bed_id,
                'last_message_at' => now(),
            ],
        );

        $thread->messages()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $booking->host_user_id,
            'sender_user_id' => $booking->host_user_id,
            'recipient_user_id' => $booking->guest_user_id,
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'body' => $bodyKey,
            'is_system_message' => true,
            'system_message' => true,
            'locale' => app()->getLocale(),
        ]);

        $thread->forceFill(['last_message_at' => now()])->save();
        $conversation->forceFill(['last_message_at' => now()])->save();
    }
}
