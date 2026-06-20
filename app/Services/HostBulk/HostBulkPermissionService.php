<?php

namespace App\Services\HostBulk;

use App\Models\Booking;
use App\Models\HostCleaningTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

class HostBulkPermissionService
{
    public function ensureHostOwnsProperty(User $host, Property $property): void
    {
        if (! $property->isOwnedBy($host)) {
            throw new AuthorizationException(__('host_bulk.errors.not_allowed'));
        }
    }

    public function ensureHostOwnsRoom(User $host, Room $room): void
    {
        $room->loadMissing('property:id,host_user_id,user_id');
        $this->ensureHostOwnsProperty($host, $room->property);
    }

    public function ensureHostOwnsSleepingPlace(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,host_user_id,user_id');
        $this->ensureHostOwnsProperty($host, $place->property);
    }

    public function ensureHostCanMessageBookingGuest(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('host_bulk.errors.not_allowed'));
        }
    }

    public function ensureHostOwnsTarget(User $host, string $targetType, int $targetId): Model
    {
        $target = $this->findTarget($targetType, $targetId);

        match ($targetType) {
            'property' => $this->ensureHostOwnsProperty($host, $target),
            'room' => $this->ensureHostOwnsRoom($host, $target),
            'sleeping_place' => $this->ensureHostOwnsSleepingPlace($host, $target),
            'booking' => $this->ensureHostCanMessageBookingGuest($host, $target),
            'cleaning_task' => $this->ensureHostOwnsCleaningTask($host, $target),
            default => throw new AuthorizationException(__('host_bulk.errors.not_allowed')),
        };

        return $target;
    }

    public function ensureTargetBelongsToHost(User $host, string $targetType, int $targetId): Model
    {
        return $this->ensureHostOwnsTarget($host, $targetType, $targetId);
    }

    public function hasActiveBookingConflict(SleepingPlace $place, array $range): bool
    {
        return Booking::query()
            ->where('sleeping_place_id', $place->id)
            ->whereIn('status', $this->blockingBookingStatuses())
            ->whereDate('check_in_date', '<', $range['end'])
            ->whereDate('check_out_date', '>', $range['start'])
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function blockingBookingStatuses(): array
    {
        return [
            'awaiting_payment',
            'pending_payment',
            'confirmed',
            'paid',
            'ready_for_checkin',
            'checked_in',
            'in_progress',
            'active_stay',
        ];
    }

    private function ensureHostOwnsCleaningTask(User $host, HostCleaningTask $task): void
    {
        if ((int) $task->user_id !== (int) $host->id) {
            throw new AuthorizationException(__('host_bulk.errors.not_allowed'));
        }
    }

    private function findTarget(string $targetType, int $targetId): Model
    {
        return match ($targetType) {
            'property' => Property::query()->findOrFail($targetId),
            'room' => Room::query()->with('property:id,host_user_id,user_id')->findOrFail($targetId),
            'sleeping_place' => SleepingPlace::query()->with('property:id,host_user_id,user_id')->findOrFail($targetId),
            'booking' => Booking::query()->findOrFail($targetId),
            'cleaning_task' => HostCleaningTask::query()->findOrFail($targetId),
            default => throw new AuthorizationException(__('host_bulk.errors.not_allowed')),
        };
    }
}
