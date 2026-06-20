<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;
use App\Models\HostCleaningTaskPhoto;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;

class HostCleaningPhotoService
{
    public function addBeforePhoto(User $user, HostCleaningTask $task, UploadedFile $file): HostCleaningTaskPhoto
    {
        return $this->addPhoto($user, $task, $file, 'before');
    }

    public function addAfterPhoto(User $user, HostCleaningTask $task, UploadedFile $file): HostCleaningTaskPhoto
    {
        return $this->addPhoto($user, $task, $file, 'after');
    }

    public function addDamagePhoto(User $user, HostCleaningTask $task, UploadedFile $file): HostCleaningTaskPhoto
    {
        return $this->addPhoto($user, $task, $file, 'damage');
    }

    public function addForgottenItemPhoto(User $user, HostCleaningTask $task, UploadedFile $file): HostCleaningTaskPhoto
    {
        return $this->addPhoto($user, $task, $file, 'forgotten_item');
    }

    public function deletePhoto(User $user, HostCleaningTaskPhoto $photo): void
    {
        $photo->loadMissing('task');
        $this->authorize($user, $photo->task);
        $photo->delete();
        $this->refreshPhotoFlags($photo->task);
    }

    private function addPhoto(User $user, HostCleaningTask $task, UploadedFile $file, string $type): HostCleaningTaskPhoto
    {
        $this->authorize($user, $task);

        $path = $file->store('cleaning/'.$task->id, 'public');
        $photo = HostCleaningTaskPhoto::query()->create([
            'host_cleaning_task_id' => $task->id,
            'uploaded_by_user_id' => $user->id,
            'photo_type' => $type,
            'path' => $path,
        ]);

        $this->refreshPhotoFlags($task);

        return $photo;
    }

    private function refreshPhotoFlags(HostCleaningTask $task): void
    {
        $task->forceFill([
            'has_before_photos' => $task->photos()->where('photo_type', 'before')->exists(),
            'has_after_photos' => $task->photos()->where('photo_type', 'after')->exists(),
        ])->save();
    }

    private function authorize(User $user, HostCleaningTask $task): void
    {
        if ((int) $task->user_id !== (int) $user->id && (int) $task->assigned_to_user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
