<?php

namespace App\Services\Cleaning;

use App\Models\InspectionTask;
use App\Models\InspectionTaskMedia;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class InspectionPrivacyService
{
    public function canHostView(User $host, InspectionTask $task): bool
    {
        return (int) $task->host_user_id === (int) $host->id;
    }

    public function canHostManage(User $host, InspectionTask $task): bool
    {
        return $this->canHostView($host, $task);
    }

    public function canViewMedia(User $user, InspectionTaskMedia $media): bool
    {
        $task = $media->inspectionTask;

        if (! $task || in_array($media->visibility, ['internal', 'future_review_only'], true)) {
            return false;
        }

        if ($this->canHostView($user, $task)) {
            return true;
        }

        return $media->visibility === 'guest_and_host'
            && $task->booking()
                ->where('guest_user_id', $user->id)
                ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, InspectionTask $task): array
    {
        $this->ensureCanManage($host, $task);

        return $task->fresh(['items', 'media', 'property', 'room', 'sleepingPlace'])->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, InspectionTask $task): array
    {
        if (! $task->booking()->where('guest_user_id', $guest->id)->exists()) {
            throw new AuthorizationException;
        }

        return [
            'id' => $task->id,
            'booking_id' => $task->booking_id,
            'status' => $task->passed ? 'ready' : 'preparing',
            'message_key' => $task->passed ? 'readiness.messages.place_ready' : 'readiness.messages.place_not_ready',
        ];
    }

    public function ensureCanManage(User $user, InspectionTask $task): void
    {
        if (! $this->canHostManage($user, $task) && (int) $task->responsible_user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
