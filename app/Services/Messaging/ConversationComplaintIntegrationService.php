<?php

namespace App\Services\Messaging;

use App\Models\ComplaintCase;
use App\Models\Conversation;

class ConversationComplaintIntegrationService
{
    public function createConversationForComplaint(ComplaintCase $case): Conversation
    {
        return app(ConversationService::class)->createForComplaint($case);
    }

    public function addComplaintOpenedEvent(ComplaintCase $case): void
    {
        app(ConversationSystemEventService::class)->addComplaintEvent($case, 'complaint_opened', [
            'importance_level' => 'important',
        ]);
    }

    public function addComplaintResolvedEvent(ComplaintCase $case): void
    {
        app(ConversationSystemEventService::class)->addComplaintEvent($case, 'complaint_resolved');
    }
}
