<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\DisputeCase;

class ConversationDisputeIntegrationService
{
    public function createConversationForDispute(DisputeCase $case): Conversation
    {
        return app(ConversationService::class)->createForDispute($case);
    }

    public function addDisputeOpenedEvent(DisputeCase $case): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($case->booking()->firstOrFail(), 'dispute_opened', [
            'source_type' => 'dispute',
            'source_id' => $case->id,
            'importance_level' => 'important',
        ]);
    }

    public function addDisputeResolvedEvent(DisputeCase $case): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($case->booking()->firstOrFail(), 'dispute_resolved', [
            'source_type' => 'dispute',
            'source_id' => $case->id,
        ]);
    }
}
