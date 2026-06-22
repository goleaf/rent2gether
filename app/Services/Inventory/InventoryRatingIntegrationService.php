<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryIssue;

class InventoryRatingIntegrationService
{
    public function recordConfirmedInventoryDamage(InventoryIssue $issue): void
    {
        if ($issue->guest_responsibility_status !== 'confirmed_guest_fault') {
            return;
        }

        app(InventoryEventService::class)->recordForIssue($issue, 'inventory_damage_rating_recorded');
    }

    public function recordInventoryReturnedClean(BookingInventoryAssignment $assignment): void
    {
        if ($assignment->status !== 'returned') {
            return;
        }

        app(InventoryEventService::class)->recordForAssignment($assignment, 'inventory_returned_clean_rating_recorded');
    }

    public function removeRatingImpactIfGuestFaultRejected(InventoryIssue $issue): void
    {
        if (! in_array($issue->guest_responsibility_status, ['guest_disputed', 'rejected_guest_fault'], true)) {
            return;
        }

        app(InventoryEventService::class)->recordForIssue($issue, 'inventory_rating_impact_removed');
    }
}
