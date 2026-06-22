<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskMedia;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CleaningPrivacyService
{
    public function canHostView(User $host, CleaningTask $task): bool
    {
        return (int) $task->host_user_id === (int) $host->id;
    }

    public function canHostManage(User $host, CleaningTask $task): bool
    {
        return $this->canHostView($host, $task);
    }

    public function canResponsibleView(?User $user, CleaningTask $task): bool
    {
        return $user !== null && (int) $task->responsible_user_id === (int) $user->id;
    }

    public function canViewMedia(User $user, CleaningTaskMedia $media): bool
    {
        $task = $media->cleaningTask;

        if (! $task || in_array($media->visibility, ['internal', 'future_review_only'], true)) {
            return false;
        }

        if ($this->canHostView($user, $task)) {
            return true;
        }

        if ($media->visibility !== 'guest_and_host') {
            return false;
        }

        return $task->booking()
            ->where('guest_user_id', $user->id)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, CleaningTask $task): array
    {
        $this->ensureCanView($host, $task);

        return $task->fresh([
            'items',
            'media',
            'issues',
            'property',
            'room',
            'sleepingPlace',
        ])->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, CleaningTask $task): array
    {
        $bookingBelongsToGuest = $task->booking()
            ->where('guest_user_id', $guest->id)
            ->exists();

        if (! $bookingBelongsToGuest) {
            throw new AuthorizationException;
        }

        return [
            'id' => $task->id,
            'booking_id' => $task->booking_id,
            'status' => in_array($task->status, ['completed', 'closed'], true) ? 'ready' : 'preparing',
            'message_key' => in_array($task->status, ['completed', 'closed'], true)
                ? 'readiness.messages.place_ready'
                : 'readiness.messages.place_not_ready',
            'is_safe_notice' => true,
        ];
    }

    public function ensureCanView(User $user, CleaningTask $task): void
    {
        if (! $this->canHostView($user, $task) && ! $this->canResponsibleView($user, $task)) {
            throw new AuthorizationException;
        }
    }

    public function ensureCanManage(User $user, CleaningTask $task): void
    {
        if (! $this->canHostManage($user, $task) && ! $this->canResponsibleView($user, $task)) {
            throw new AuthorizationException;
        }
    }
}
