<?php

namespace App\Services\Cleaning;

use App\Models\InspectionTask;

class InspectionNotificationService
{
    public function notifyInspectionCreated(InspectionTask $task): void {}

    public function notifyInspectionPassed(InspectionTask $task): void {}

    public function notifyInspectionFailed(InspectionTask $task): void {}

    public function notifyPlaceReady(InspectionTask $task): void {}
}
