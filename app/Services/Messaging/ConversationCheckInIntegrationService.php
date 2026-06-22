<?php

namespace App\Services\Messaging;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInProblem;

class ConversationCheckInIntegrationService
{
    public function addCheckInInstructionAvailableEvent(BookingCheckIn $checkIn): void
    {
        app(ConversationSystemEventService::class)->addCheckInEvent($checkIn, 'check_in_instruction_available');
    }

    public function addGuestArrivedEvent(BookingCheckIn $checkIn): void
    {
        app(ConversationSystemEventService::class)->addCheckInEvent($checkIn, 'guest_arrived', [
            'importance_level' => 'important',
        ]);
    }

    public function addCheckInProblemEvent(BookingCheckInProblem $problem): void
    {
        app(ConversationSystemEventService::class)->addCheckInEvent($problem->checkIn()->firstOrFail(), 'check_in_problem', [
            'source_type' => 'check_in',
            'source_id' => $problem->id,
            'importance_level' => 'urgent',
        ]);
    }

    public function addCheckInConfirmedEvent(BookingCheckIn $checkIn): void
    {
        app(ConversationSystemEventService::class)->addCheckInEvent($checkIn, 'check_in_confirmed');
    }
}
