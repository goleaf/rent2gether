<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;

class HostCleaningInspectionService
{
    public function requiresInspection(HostCleaningTask $task): bool
    {
        return $task->status === 'waiting_for_inspection'
            || $task->has_damage_found
            || $task->has_extra_dirty
            || $task->needs_repair;
    }
}
