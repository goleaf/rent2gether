<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;

class HostCleaningReadinessService
{
    public function canCompleteCleaning(HostCleaningTask $task): bool
    {
        return $this->getBlockingIssues($task) === [];
    }

    public function canMarkPlaceReady(HostCleaningTask $task): bool
    {
        $task->loadMissing(['findings', 'photos', 'items']);

        if ($task->status !== 'done') {
            return false;
        }

        if (! $this->canCompleteCleaning($task)) {
            return false;
        }

        return ! $task->has_damage_found
            && ! $task->has_forgotten_items
            && ! $task->has_extra_dirty
            && ! $task->needs_repair
            && ! $task->needs_repeat_cleaning
            && ! $task->findings
                ->where('status', 'open')
                ->contains(fn ($finding): bool => $finding->needs_host_action || $finding->needs_repair || $finding->needs_deposit_review);
    }

    public function getBlockingIssues(HostCleaningTask $task): array
    {
        $task->loadMissing(['items', 'photos']);

        $issues = [];
        $missingItems = $task->items
            ->where('required', true)
            ->reject(fn ($item): bool => $item->status === 'done')
            ->values();

        if ($missingItems->isNotEmpty()) {
            $issues[] = 'missing_required_items';
        }

        if ($task->before_photos_required && ! $task->has_before_photos && ! $task->photos->where('photo_type', 'before')->count()) {
            $issues[] = 'missing_before_photo';
        }

        if ($task->after_photos_required && ! $task->has_after_photos && ! $task->photos->where('photo_type', 'after')->count()) {
            $issues[] = 'missing_after_photo';
        }

        return $issues;
    }

    public function getRecommendedIssues(HostCleaningTask $task): array
    {
        $task->loadMissing('photos');
        $issues = [];

        if (! $task->photos->where('photo_type', 'mattress')->count()) {
            $issues[] = 'add_mattress_photo';
        }

        if (! $task->photos->where('photo_type', 'locker')->count()) {
            $issues[] = 'add_locker_photo';
        }

        return $issues;
    }
}
