<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use App\Models\HostCleaningTask;

class ListingMismatchCleaningIntegrationService
{
    public function createCleaningIfNeeded(BookingListingMismatchReport $report): mixed
    {
        if (! in_array($report->mismatch_type, ['dirty_sleeping_place', 'dirty_room', 'dirty_property', 'bad_smell', 'mold', 'insects'], true)) {
            return null;
        }

        $task = HostCleaningTask::query()->create([
            'user_id' => $report->host_user_id,
            'property_id' => $report->property_id,
            'room_id' => $report->room_id,
            'sleeping_place_id' => $report->sleeping_place_id,
            'booking_id' => $report->booking_id,
            'cleaning_type' => 'after_complaint',
            'status' => 'planned',
            'priority' => in_array($report->severity, ['urgent', 'unsafe'], true) ? 'urgent' : 'high',
            'scheduled_date' => now()->toDateString(),
            'due_at' => now()->addHours(2),
            'reason' => 'listing_mismatch',
            'note' => $report->guest_description,
            'after_photos_required' => true,
            'has_extra_dirty' => true,
        ]);

        $report->forceFill([
            'cleaning_task_id' => $task->id,
            'resolution_type' => 'cleaning',
            'resolution_status' => 'in_progress',
        ])->save();

        app(ListingMismatchEventService::class)->record($report->fresh(), 'cleaning_created', ['cleaning_task_id' => $task->id]);

        return $task->fresh();
    }

    public function markCleaningResolutionCompleted(BookingListingMismatchReport $report): void
    {
        $report->forceFill([
            'status' => 'fixed',
            'resolution_status' => 'completed',
            'resolved_at' => now(),
        ])->save();
    }
}
