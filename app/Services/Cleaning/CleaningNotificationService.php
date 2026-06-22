<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;

class CleaningNotificationService
{
    public function notifyCleaningCreated(CleaningTask $task): void {}

    public function notifyCleaningAssigned(CleaningTask $task): void {}

    public function notifyCleaningCompleted(CleaningTask $task): void {}

    public function notifyCleaningIssueFound(CleaningTask $task): void {}

    public function notifyPlaceBlocked(CleaningTask $task): void {}
}
