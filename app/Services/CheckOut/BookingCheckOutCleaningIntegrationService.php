<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Services\HostCleaning\HostCleaningTaskService;

class BookingCheckOutCleaningIntegrationService
{
    public function createCleaningAfterCheckout(BookingCheckOut $checkOut): mixed
    {
        $task = app(HostCleaningTaskService::class)->createAfterCheckout($checkOut);
        $checkOut->forceFill([
            'cleaning_required' => true,
            'cleaning_task_id' => $task?->id,
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'cleaning_created');

        return $task;
    }

    public function createUrgentCleaningIfSameDayTurnover(BookingCheckOut $checkOut): mixed
    {
        return $this->createCleaningAfterCheckout($checkOut);
    }

    public function cancelCleaningIfCheckoutCancelled(BookingCheckOut $checkOut): void
    {
        $checkOut->forceFill([
            'cleaning_required' => false,
            'cleaning_task_id' => null,
        ])->save();
    }
}
