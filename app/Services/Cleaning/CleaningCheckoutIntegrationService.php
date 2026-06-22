<?php

namespace App\Services\Cleaning;

use App\Models\BookingCheckOut;
use App\Models\CleaningTask;
use App\Models\InspectionTask;

class CleaningCheckoutIntegrationService
{
    public function createCleaningAfterCheckout(BookingCheckOut $checkOut): CleaningTask
    {
        return app(CleaningTaskService::class)->createAfterCheckout($checkOut);
    }

    public function createInspectionAfterCheckoutIfRequired(BookingCheckOut $checkOut): ?InspectionTask
    {
        if (! $checkOut->inspection_required && ! $checkOut->repair_required) {
            return null;
        }

        return app(InspectionTaskService::class)->createPostCheckout($checkOut);
    }

    public function syncCheckoutAfterCleaning(CleaningTask $task): void
    {
        $task->checkOut?->forceFill([
            'cleaning_required' => $task->status !== 'completed',
        ])->save();
    }
}
