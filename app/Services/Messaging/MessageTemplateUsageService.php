<?php

namespace App\Services\Messaging;

use App\Models\BookingCheckOut;
use App\Models\BookingExtension;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\ConversationMessage;
use App\Models\MessageTemplate;
use App\Models\MessageTemplateUsage;
use App\Models\User;
use App\Services\Bookings\BookingExtensionNumberService;
use App\Services\Bookings\HostUnresponsiveNumberService;
use App\Services\CheckOut\BookingCheckOutService;

class MessageTemplateUsageService
{
    public function recordUsage(MessageTemplate $template, ConversationMessage $message, ?User $user = null): MessageTemplateUsage
    {
        $usage = MessageTemplateUsage::query()->create([
            'message_template_id' => $template->id,
            'template_key' => $template->template_key,
            'conversation_id' => $message->conversation_id,
            'conversation_message_id' => $message->id,
            'user_id' => $user?->id,
            'booking_id' => $message->booking_id,
            'source_type' => $message->source_type,
            'source_id' => $message->source_id,
            'used_at' => now(),
        ]);

        $this->triggerTemplateActionIfNeeded($usage);

        return $usage;
    }

    public function triggerTemplateActionIfNeeded(MessageTemplateUsage $usage): mixed
    {
        $usage->loadMissing(['template', 'conversation.booking']);
        $booking = $usage->conversation?->booking;

        if (! $booking || ! $usage->template?->creates_action) {
            return null;
        }

        return match ($usage->template->action_type) {
            'open_host_unresponsive' => $this->createHostUnresponsiveCase($usage),
            'open_extension' => $this->createExtension($usage),
            'open_checkout' => $this->createCheckout($usage),
            default => null,
        };
    }

    private function createHostUnresponsiveCase(MessageTemplateUsage $usage): BookingHostUnresponsiveCase
    {
        $booking = $usage->conversation->booking;

        return BookingHostUnresponsiveCase::query()->firstOrCreate([
            'booking_id' => $booking->id,
            'status' => 'reported',
        ], [
            'case_number' => app(HostUnresponsiveNumberService::class)->generate(),
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'case_type' => 'check_in_no_response',
            'reason_key' => 'host_not_answering_messages',
            'check_in_date' => $booking->check_in_date ?: $booking->check_in,
            'planned_check_in_time' => $this->timeString($booking->check_in_time ?: $booking->arrival_time),
            'response_deadline_minutes' => 60,
            'response_deadline_at' => now()->addHour(),
            'guest_wants_help' => true,
            'currency' => $booking->currency ?: 'EUR',
        ]);
    }

    private function createExtension(MessageTemplateUsage $usage): BookingExtension
    {
        $booking = $usage->conversation->booking;
        $current = $booking->check_out_date ?: $booking->check_out ?: now()->addDay();
        $new = $current->copy()->addDay();

        return BookingExtension::query()->firstOrCreate([
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'status' => 'draft',
        ], [
            'extension_number' => app(BookingExtensionNumberService::class)->generate(),
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'current_checkout_date' => $current->toDateString(),
            'requested_new_checkout_date' => $new->toDateString(),
            'current_check_out_date' => $current->toDateString(),
            'new_check_out_date' => $new->toDateString(),
            'original_check_out' => $current->toDateString(),
            'new_check_out' => $new->toDateString(),
            'additional_nights' => 1,
            'additional_nights_count' => 1,
            'additional_chargeable_days_count' => 1,
            'additional_calendar_presence_days_count' => 2,
            'extra_nights' => 1,
            'extension_type' => 'chat_template_request',
            'requires_host_approval' => true,
            'requires_host_confirmation' => true,
            'requires_payment' => false,
            'payment_required' => false,
            'currency' => $booking->currency ?: 'EUR',
            'extra_amount' => 0,
            'additional_amount' => 0,
            'discount_amount' => 0,
            'accommodation_amount' => 0,
            'service_fee_amount' => 0,
            'cleaning_fee_amount' => 0,
            'additional_deposit_amount' => 0,
            'total_payable' => 0,
            'total_extra' => 0,
            'host_payout_amount' => 0,
            'refundable_amount' => 0,
            'non_refundable_amount' => 0,
            'hold_dates' => true,
            'payment_status' => 'not_required',
        ]);
    }

    private function createCheckout(MessageTemplateUsage $usage): BookingCheckOut
    {
        return app(BookingCheckOutService::class)->createForBooking($usage->conversation->booking);
    }

    private function timeString(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface ? $value->format('H:i') : $value;
    }
}
