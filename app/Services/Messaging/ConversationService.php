<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\CleaningTask;
use App\Models\ComplaintCase;
use App\Models\Conversation;
use App\Models\DisputeCase;
use App\Models\InspectionTask;
use App\Models\InventoryIssue;
use App\Models\SleepingPlace;
use App\Models\User;

class ConversationService
{
    public function __construct(
        private readonly ConversationNumberService $numbers,
        private readonly ConversationParticipantService $participants,
        private readonly ConversationEventService $events,
    ) {}

    public function createForListingInquiry(User $guest, SleepingPlace $place): Conversation
    {
        $place->loadMissing(['property.host', 'room']);
        $host = $place->property?->host;

        return $this->createOrFind([
            'conversation_type' => 'listing_inquiry',
            'guest' => $guest,
            'host' => $host,
            'property' => $place->property,
            'room' => $place->room,
            'sleeping_place' => $place,
        ]);
    }

    public function createForBooking(Booking $booking): Conversation
    {
        return $this->getOrCreateForBooking($booking);
    }

    public function getOrCreateForBooking(Booking $booking): Conversation
    {
        $booking->loadMissing(['guest', 'host', 'property', 'room', 'sleepingPlace']);

        return $this->createOrFind([
            'conversation_type' => 'booking',
            'booking' => $booking,
            'guest' => $booking->guest,
            'host' => $booking->host,
            'property' => $booking->property,
            'room' => $booking->room,
            'sleeping_place' => $booking->sleepingPlace,
        ]);
    }

    public function createForCheckIn(BookingCheckIn $checkIn): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($checkIn, 'check_in') + [
            'booking_check_in_id' => $checkIn->id,
        ]);
    }

    public function createForCheckOut(BookingCheckOut $checkOut): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($checkOut, 'check_out') + [
            'booking_check_out_id' => $checkOut->id,
        ]);
    }

    public function createForComplaint(ComplaintCase $complaint): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($complaint, 'complaint') + [
            'complaint_case_id' => $complaint->id,
        ]);
    }

    public function createForDispute(DisputeCase $dispute): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($dispute, 'dispute') + [
            'dispute_case_id' => $dispute->id,
        ]);
    }

    public function createForDeposit(mixed $deposit): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($deposit, 'deposit') + [
            'booking_deposit_id' => $deposit->id ?? null,
        ]);
    }

    public function createForMaintenance(mixed $request): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($request, 'maintenance') + [
            'maintenance_request_id' => $request->id ?? null,
        ]);
    }

    public function createForInventoryIssue(InventoryIssue $issue): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($issue, 'inventory') + [
            'inventory_issue_id' => $issue->id,
        ]);
    }

    public function closeConversation(Conversation $conversation): Conversation
    {
        return app(ConversationStatusService::class)->transition($conversation, 'closed');
    }

    public function archiveForUser(User $user, Conversation $conversation): void
    {
        $conversation->participants()
            ->where('user_id', $user->id)
            ->update(['archived' => true]);

        $conversation->update(['archived_at' => now()]);
        $this->events->record($conversation, 'conversation_archived', ['user_id' => $user->id]);
    }

    public function createForCleaning(CleaningTask $task): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($task, 'cleaning_readiness') + [
            'cleaning_task_id' => $task->id,
        ]);
    }

    public function createForInspection(InspectionTask $task): Conversation
    {
        return $this->createOrFind($this->contextFromWorkflow($task, 'cleaning_readiness') + [
            'inspection_task_id' => $task->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function createOrFind(array $context): Conversation
    {
        $guest = $context['guest'] ?? null;
        $host = $context['host'] ?? null;
        $booking = $context['booking'] ?? null;
        $property = $context['property'] ?? null;
        $room = $context['room'] ?? null;
        $sleepingPlace = $context['sleeping_place'] ?? null;
        $conversationType = (string) $context['conversation_type'];

        $existing = Conversation::query()
            ->when($booking instanceof Booking, fn ($query) => $query->where('booking_id', $booking->id))
            ->when(! $booking instanceof Booking, fn ($query) => $query
                ->where('conversation_type', $conversationType)
                ->where('guest_user_id', $guest?->id)
                ->where('host_user_id', $host?->id)
                ->where('sleeping_place_id', $sleepingPlace?->id))
            ->where('conversation_type', $conversationType)
            ->first();

        if ($existing instanceof Conversation) {
            return $existing;
        }

        $participantIds = $this->legacyParticipantIds($guest, $host);

        $conversation = Conversation::query()->create([
            'participant_one_id' => $participantIds[0],
            'participant_two_id' => $participantIds[1],
            'conversation_number' => $this->numbers->generateConversationNumber(),
            'conversation_type' => $conversationType,
            'status' => 'active',
            'guest_user_id' => $guest?->id,
            'host_user_id' => $host?->id,
            'property_id' => $property?->id,
            'room_id' => $room?->id,
            'sleeping_place_id' => $sleepingPlace?->id,
            'booking_id' => $booking?->id,
            'booking_stay_id' => $context['booking_stay_id'] ?? null,
            'booking_check_in_id' => $context['booking_check_in_id'] ?? null,
            'booking_check_out_id' => $context['booking_check_out_id'] ?? null,
            'complaint_case_id' => $context['complaint_case_id'] ?? null,
            'dispute_case_id' => $context['dispute_case_id'] ?? null,
            'booking_deposit_id' => $context['booking_deposit_id'] ?? null,
            'maintenance_request_id' => $context['maintenance_request_id'] ?? null,
            'inventory_issue_id' => $context['inventory_issue_id'] ?? null,
            'cleaning_task_id' => $context['cleaning_task_id'] ?? null,
            'inspection_task_id' => $context['inspection_task_id'] ?? null,
            'guest_unread_count' => 0,
            'host_unread_count' => 0,
            'has_urgent_messages' => false,
            'has_important_messages' => false,
            'guest_can_write' => true,
            'host_can_write' => true,
            'is_read_only' => false,
            'is_system_only' => false,
            'last_message_at' => now(),
        ]);

        if ($guest instanceof User) {
            $this->participants->addGuest($conversation, $guest);
        }

        if ($host instanceof User) {
            $this->participants->addHost($conversation, $host);
        }

        $this->participants->addSystem($conversation);
        $this->events->record($conversation, 'conversation_created');

        return $conversation;
    }

    /**
     * @return array<string, mixed>
     */
    private function contextFromWorkflow(mixed $model, string $conversationType): array
    {
        $relations = collect(['booking', 'guest', 'host', 'property', 'room', 'sleepingPlace'])
            ->filter(fn (string $relation): bool => is_object($model) && method_exists($model, $relation))
            ->all();

        if ($relations !== [] && method_exists($model, 'loadMissing')) {
            $model->loadMissing($relations);
        }

        return [
            'conversation_type' => $conversationType,
            'booking' => $model->booking ?? null,
            'guest' => $model->guest ?? null,
            'host' => $model->host ?? null,
            'property' => $model->property ?? null,
            'room' => $model->room ?? null,
            'sleeping_place' => $model->sleepingPlace ?? null,
            'booking_stay_id' => $model->booking_stay_id ?? null,
        ];
    }

    /**
     * @return array{int, int}
     */
    private function legacyParticipantIds(?User $guest, ?User $host): array
    {
        $first = $guest?->id ?? $host?->id ?? User::query()->value('id');
        $second = $host?->id ?? $guest?->id ?? $first;

        return [
            min((int) $first, (int) $second),
            max((int) $first, (int) $second),
        ];
    }
}
