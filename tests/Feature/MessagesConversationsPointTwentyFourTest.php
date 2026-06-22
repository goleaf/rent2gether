<?php

namespace Tests\Feature;

use App\Livewire\Host\Messages\HostUrgentMessagesPanel;
use App\Livewire\Messages\ConversationPage;
use App\Livewire\Messages\QuickTemplatePicker;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\BookingDepositDecision;
use App\Models\BookingExtension;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingPayment;
use App\Models\ComplaintCase;
use App\Models\ConversationMessage;
use App\Models\ConversationSafetyWarning;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Services\Messaging\ConversationAttachmentService;
use App\Services\Messaging\ConversationBookingIntegrationService;
use App\Services\Messaging\ConversationCheckInIntegrationService;
use App\Services\Messaging\ConversationComplaintIntegrationService;
use App\Services\Messaging\ConversationDepositIntegrationService;
use App\Services\Messaging\ConversationMessageService;
use App\Services\Messaging\ConversationParticipantService;
use App\Services\Messaging\ConversationPaymentIntegrationService;
use App\Services\Messaging\ConversationPrivacyService;
use App\Services\Messaging\ConversationReadService;
use App\Services\Messaging\ConversationResponseTimeService;
use App\Services\Messaging\ConversationSafetyService;
use App\Services\Messaging\ConversationSearchService;
use App\Services\Messaging\ConversationService;
use App\Services\Messaging\ConversationStatusService;
use App\Services\Messaging\MessageTemplateService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Tests\TestCase;

class MessagesConversationsPointTwentyFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversations_can_be_created_for_listing_inquiry_and_booking_with_context(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace, $booking] = $this->createConversationContext();
        $service = app(ConversationService::class);

        $listingConversation = $service->createForListingInquiry($guest, $sleepingPlace);
        $bookingConversation = $service->createForBooking($booking);
        $sameBookingConversation = $service->getOrCreateForBooking($booking);

        $this->assertSame('listing_inquiry', $listingConversation->conversation_type);
        $this->assertSame($guest->id, $listingConversation->guest_user_id);
        $this->assertSame($host->id, $listingConversation->host_user_id);
        $this->assertSame($property->id, $listingConversation->property_id);
        $this->assertSame($room->id, $listingConversation->room_id);
        $this->assertSame($sleepingPlace->id, $listingConversation->sleeping_place_id);
        $this->assertSame('booking', $bookingConversation->conversation_type);
        $this->assertSame($bookingConversation->id, $sameBookingConversation->id);
        $this->assertTrue(app(ConversationPrivacyService::class)->canViewConversation($guest, $bookingConversation));
        $this->assertTrue(app(ConversationPrivacyService::class)->canViewConversation($host, $bookingConversation));
        $this->assertFalse(app(ConversationPrivacyService::class)->canViewConversation(User::factory()->create(), $bookingConversation));
    }

    public function test_participants_can_send_messages_and_read_receipts_update_unread_counters(): void
    {
        [$guest, $host, , , , $booking] = $this->createConversationContext();
        $conversation = app(ConversationService::class)->createForBooking($booking);
        $messageService = app(ConversationMessageService::class);

        $message = $messageService->sendText($guest, $conversation, 'I will arrive around 18:00', [
            'is_urgent' => true,
            'source_type' => 'booking',
            'source_id' => $booking->id,
        ]);

        $conversation->refresh();

        $this->assertSame($message->id, $conversation->last_message_id);
        $this->assertSame(1, $conversation->host_unread_count);
        $this->assertTrue($conversation->has_urgent_messages);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'conversation_urgent_message',
        ]);

        $read = app(ConversationReadService::class)->markMessageRead($host, $message);

        $this->assertSame($host->id, $read->user_id);
        $this->assertSame(0, $conversation->fresh()->host_unread_count);

        $this->expectException(AuthorizationException::class);
        $messageService->sendText(User::factory()->create(), $conversation, 'I should not send this');
    }

    public function test_guest_and_host_quick_templates_create_messages_and_related_actions(): void
    {
        [$guest, $host, , , , $booking] = $this->createConversationContext();
        $conversation = app(ConversationService::class)->createForBooking($booking);
        $templateService = app(MessageTemplateService::class);

        $templateService->seedDefaultTemplates();

        $guestTemplates = $templateService->getAvailableForUser($guest, $conversation);
        $hostTemplates = $templateService->getAvailableForUser($host, $conversation);

        $this->assertTrue($guestTemplates->contains('template_key', 'host_not_answering'));
        $this->assertTrue($hostTemplates->contains('template_key', 'booking_confirmed'));

        app(ConversationMessageService::class)->sendTemplate($guest, $conversation, 'host_not_answering');
        app(ConversationMessageService::class)->sendTemplate($guest, $conversation, 'can_extend_stay');
        app(ConversationMessageService::class)->sendTemplate($guest, $conversation, 'i_checked_out');
        $hostMessage = app(ConversationMessageService::class)->sendTemplate($host, $conversation, 'booking_confirmed');

        $this->assertSame('quick_template', $hostMessage->message_type);
        $this->assertDatabaseHas('message_template_usages', [
            'conversation_id' => $conversation->id,
            'template_key' => 'host_not_answering',
        ]);
        $this->assertDatabaseHas((new BookingHostUnresponsiveCase)->getTable(), [
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
        ]);
        $this->assertDatabaseHas((new BookingExtension)->getTable(), [
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
        ]);
        $this->assertDatabaseHas((new BookingCheckOut)->getTable(), [
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
        ]);
    }

    public function test_system_events_are_added_with_translation_keys_for_booking_payment_checkin_deposit_and_complaint(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace, $booking] = $this->createConversationContext();
        $conversation = app(ConversationService::class)->createForBooking($booking);
        $payment = BookingPayment::factory()->for($booking)->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $sleepingPlace->id,
        ]);
        $checkIn = BookingCheckIn::factory()->for($booking)->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $sleepingPlace->id,
        ]);
        $depositDecision = BookingDepositDecision::factory()->for($booking)->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
        ]);
        $complaint = ComplaintCase::factory()->for($booking)->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $sleepingPlace->id,
        ]);

        app(ConversationBookingIntegrationService::class)->addBookingCreatedEvent($booking);
        app(ConversationPaymentIntegrationService::class)->addPaymentCompletedEvent($payment);
        app(ConversationCheckInIntegrationService::class)->addCheckInInstructionAvailableEvent($checkIn);
        app(ConversationDepositIntegrationService::class)->addDepositDeductionRequestedEvent($depositDecision);
        app(ConversationComplaintIntegrationService::class)->addComplaintOpenedEvent($complaint);

        $translationKeys = ConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('message_type', 'system_event')
            ->pluck('translation_key')
            ->all();

        $this->assertContains('messages.system_events.booking_created', $translationKeys);
        $this->assertContains('messages.system_events.payment_completed', $translationKeys);
        $this->assertContains('messages.system_events.check_in_instruction_available', $translationKeys);
        $this->assertContains('messages.system_events.deposit_deduction_requested', $translationKeys);
        $this->assertContains('messages.system_events.complaint_opened', $translationKeys);
        $this->assertSame('Booking created.', Lang::get('messages.system_events.booking_created', [], 'en'));
        $this->assertSame('Бронирование создано.', Lang::get('messages.system_events.booking_created', [], 'ru'));
    }

    public function test_internal_notes_attachments_and_safety_warnings_respect_privacy(): void
    {
        [$guest, $host, , , , $booking] = $this->createConversationContext();
        $conversation = app(ConversationService::class)->createForBooking($booking);
        $privacy = app(ConversationPrivacyService::class);
        $messageService = app(ConversationMessageService::class);

        $internalNote = $messageService->sendInternalNote($host, $conversation, 'Guest asked for lower bunk.');
        $publicMessage = $messageService->sendText($guest, $conversation, 'Can I pay by direct bank transfer? Door code is 4812.');

        $hostOnlyAttachment = app(ConversationAttachmentService::class)->attachPhoto($host, $publicMessage, [
            'path' => 'messages/private.jpg',
            'visibility' => 'host_only',
        ]);
        $futureReviewAttachment = app(ConversationAttachmentService::class)->attachPhoto($host, $publicMessage, [
            'path' => 'messages/review.jpg',
            'visibility' => 'future_review_only',
        ]);

        $warnings = app(ConversationSafetyService::class)->checkMessageBeforeSend($guest, $conversation, $publicMessage->body);

        $this->assertTrue($privacy->canViewMessage($host, $internalNote));
        $this->assertFalse($privacy->canViewMessage($guest, $internalNote));
        $this->assertTrue($privacy->canViewAttachment($host, $hostOnlyAttachment));
        $this->assertFalse($privacy->canViewAttachment($guest, $hostOnlyAttachment));
        $this->assertFalse($privacy->canViewAttachment($host, $futureReviewAttachment));
        $this->assertTrue($warnings->contains('warning_key', 'possible_off_platform_payment'));
        $this->assertTrue($warnings->contains('warning_key', 'possible_sensitive_access_details'));
        $this->assertDatabaseHas((new ConversationSafetyWarning)->getTable(), [
            'conversation_id' => $conversation->id,
            'warning_key' => 'possible_off_platform_payment',
        ]);
    }

    public function test_status_search_and_response_time_services_use_only_allowed_messages(): void
    {
        [$guest, $host, , , , $booking] = $this->createConversationContext();
        $conversation = app(ConversationService::class)->createForBooking($booking);
        $messageService = app(ConversationMessageService::class);

        $guestMessage = $messageService->sendText($guest, $conversation, 'Question before arrival');
        $messageService->sendSystemEvent($conversation, 'booking_confirmed');
        $messageService->sendInternalNote($host, $conversation, 'Do not count this as response.');
        $hostMessage = $messageService->sendText($host, $conversation, 'Answer from host', [
            'sent_at' => $guestMessage->sent_at->copy()->addMinutes(7),
        ]);

        app(ConversationResponseTimeService::class)->recordResponseTime($hostMessage);
        app(ConversationParticipantService::class)->mute($guest, $conversation);
        app(ConversationService::class)->archiveForUser($guest, $conversation);
        app(ConversationStatusService::class)->transition($conversation, 'closed', $host);

        $found = app(ConversationSearchService::class)->searchUserConversations($guest, 'arrival');
        $notFoundForOtherUser = app(ConversationSearchService::class)->searchUserConversations(User::factory()->create(), 'arrival');

        $this->assertSame(7, app(ConversationResponseTimeService::class)->calculateHostAverageResponseTime($host));
        $this->assertSame('closed', $conversation->fresh()->status);
        $this->assertTrue($conversation->participants()->where('user_id', $guest->id)->where('muted', true)->exists());
        $this->assertTrue($conversation->participants()->where('user_id', $guest->id)->where('archived', true)->exists());
        $this->assertSame($conversation->id, $found->first()->id);
        $this->assertCount(0, $notFoundForOtherUser);
    }

    public function test_livewire_message_components_render_in_english_and_russian(): void
    {
        [$guest, $host, , , , $booking] = $this->createConversationContext();
        $conversation = app(ConversationService::class)->createForBooking($booking);
        app(MessageTemplateService::class)->seedDefaultTemplates();
        app(ConversationMessageService::class)->sendText($guest, $conversation, 'The code does not work.', [
            'is_urgent' => true,
        ]);

        App::setLocale('en');
        Livewire::actingAs($guest)
            ->test(ConversationPage::class, ['conversation' => $conversation])
            ->assertSee('Messages')
            ->assertSee('The code does not work.');

        App::setLocale('ru');
        Livewire::actingAs($guest)
            ->test(QuickTemplatePicker::class, ['conversation' => $conversation])
            ->assertSee('Я скоро приеду');

        Livewire::actingAs($host)
            ->test(HostUrgentMessagesPanel::class)
            ->assertSee('Срочные')
            ->assertSee('The code does not work.');
    }

    /**
     * @return array{User, User, Property, Room, SleepingPlace, Booking}
     */
    private function createConversationContext(): array
    {
        $guest = User::factory()->create(['name' => 'Alex Guest']);
        $host = User::factory()->create(['name' => 'Host Maria', 'is_host' => true]);
        $property = Property::factory()->for($host, 'host')->create();
        $room = Room::factory()->for($property)->create();
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'display_name' => 'Bed 2',
            ]);

        SleepingPlaceTranslation::factory()->for($sleepingPlace)->create([
            'locale' => 'en',
            'title' => 'Bed 2',
        ]);
        SleepingPlaceTranslation::factory()->for($sleepingPlace)->create([
            'locale' => 'ru',
            'title' => 'Кровать 2',
        ]);

        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace)
            ->create();

        return [$guest, $host, $property, $room, $sleepingPlace, $booking];
    }
}
