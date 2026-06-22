<?php

namespace App\Services\Inventory;

use App\Models\ComplaintCase;
use App\Models\InventoryIssue;
use App\Services\Complaints\ComplaintNumberService;

class InventoryComplaintIntegrationService
{
    public function createComplaintFromInventoryIssue(InventoryIssue $issue): ?ComplaintCase
    {
        if (! class_exists(ComplaintCase::class) || ! $issue->booking_id || ! $issue->guest_user_id) {
            return null;
        }

        return ComplaintCase::query()->create([
            'complaint_number' => app(ComplaintNumberService::class)->generate(),
            'booking_id' => $issue->booking_id,
            'guest_user_id' => $issue->guest_user_id,
            'host_user_id' => $issue->host_user_id,
            'reporter_user_id' => $issue->guest_user_id,
            'property_id' => $issue->property_id,
            'room_id' => $issue->room_id,
            'sleeping_place_id' => $issue->sleeping_place_id,
            'source_type' => 'inventory_issue',
            'source_id' => $issue->id,
            'submitted_by_type' => 'guest',
            'against_type' => 'sleeping_place',
            'complaint_type' => 'inventory_problem',
            'severity' => $issue->severity,
            'status' => 'submitted',
            'description' => $issue->description ?? __('inventory.messages.issue_created'),
        ]);
    }

    public function linkComplaint(InventoryIssue $issue, ComplaintCase $complaint): void
    {
        $issue->forceFill([
            'complaint_case_id' => $complaint->id,
            'complaint_case_created_id' => $complaint->id,
            'status' => 'complaint_created',
        ])->save();
    }
}
