<?php

namespace App\Services\Messaging;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryIssue;

class ConversationInventoryIntegrationService
{
    public function addItemIssuedEvent(BookingInventoryAssignment $assignment): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($assignment->booking()->firstOrFail(), 'item_issued', [
            'source_type' => 'inventory',
            'source_id' => $assignment->id,
        ]);
    }

    public function addItemReturnExpectedEvent(BookingInventoryAssignment $assignment): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($assignment->booking()->firstOrFail(), 'item_return_expected', [
            'source_type' => 'inventory',
            'source_id' => $assignment->id,
            'importance_level' => 'important',
        ]);
    }

    public function addInventoryIssueEvent(InventoryIssue $issue): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($issue->booking()->firstOrFail(), 'inventory_issue', [
            'source_type' => 'inventory',
            'source_id' => $issue->id,
            'importance_level' => 'important',
        ]);
    }
}
