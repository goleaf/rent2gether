<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskMedia;
use App\Models\User;
use Illuminate\Support\Collection;

class CleaningTaskMediaService
{
    public function uploadBeforePhoto(User $user, CleaningTask $task, array $data): CleaningTaskMedia
    {
        return $this->createMedia($user, $task, [
            ...$data,
            'media_role' => $data['media_role'] ?? 'before_cleaning_sleeping_place',
        ], 'before_photo_uploaded');
    }

    public function uploadAfterPhoto(User $user, CleaningTask $task, array $data): CleaningTaskMedia
    {
        return $this->createMedia($user, $task, [
            ...$data,
            'media_role' => $data['media_role'] ?? 'after_cleaning_sleeping_place',
        ], 'after_photo_uploaded');
    }

    public function uploadIssueEvidence(User $user, CleaningTask $task, array $data): CleaningTaskMedia
    {
        return $this->createMedia($user, $task, [
            ...$data,
            'media_role' => $data['media_role'] ?? 'issue_evidence',
        ], 'issue_found');
    }

    public function getVisibleMedia(User $user, CleaningTask $task): Collection
    {
        return $task->media()
            ->get()
            ->filter(fn (CleaningTaskMedia $media): bool => app(CleaningPrivacyService::class)->canViewMedia($user, $media))
            ->values();
    }

    private function createMedia(User $user, CleaningTask $task, array $data, string $eventKey): CleaningTaskMedia
    {
        app(CleaningPrivacyService::class)->ensureCanManage($user, $task);

        $media = CleaningTaskMedia::query()->create([
            'cleaning_task_id' => $task->id,
            'booking_id' => $task->booking_id,
            'uploaded_by_user_id' => $user->id,
            'media_type' => $data['media_type'] ?? 'photo',
            'media_role' => $data['media_role'],
            'path' => $data['path'],
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'host_only',
        ]);

        if (str_starts_with($media->media_role, 'before_cleaning')) {
            $task->forceFill(['before_photos_uploaded' => true])->save();
        }

        if (str_starts_with($media->media_role, 'after_cleaning')) {
            $task->forceFill(['after_photos_uploaded' => true])->save();
        }

        app(CleaningEventService::class)->record($task->refresh(), $eventKey, [
            'user_id' => $user->id,
            'media_role' => $media->media_role,
        ]);

        return $media->refresh();
    }
}
