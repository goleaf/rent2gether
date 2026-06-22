<?php

namespace App\Services\Cleaning;

use App\Models\InspectionTask;
use App\Models\InspectionTaskMedia;
use App\Models\User;
use Illuminate\Support\Collection;

class InspectionTaskMediaService
{
    public function uploadPhoto(User $user, InspectionTask $task, array $data): InspectionTaskMedia
    {
        app(InspectionPrivacyService::class)->ensureCanManage($user, $task);

        $media = InspectionTaskMedia::query()->create([
            'inspection_task_id' => $task->id,
            'booking_id' => $task->booking_id,
            'uploaded_by_user_id' => $user->id,
            'media_type' => $data['media_type'] ?? 'photo',
            'media_role' => $data['media_role'] ?? 'inspection_sleeping_place',
            'path' => $data['path'],
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'host_only',
        ]);

        $task->forceFill(['photos_uploaded' => true])->save();
        app(InspectionEventService::class)->record($task->refresh(), 'photo_uploaded', ['user_id' => $user->id]);

        return $media->refresh();
    }

    public function getVisibleMedia(User $user, InspectionTask $task): Collection
    {
        return $task->media()
            ->get()
            ->filter(fn (InspectionTaskMedia $media): bool => app(InspectionPrivacyService::class)->canViewMedia($user, $media))
            ->values();
    }
}
