<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;

class HostCleaningService
{
    public function getTasks(User $host, array $filters = []): CursorPaginator
    {
        return HostCleaningTask::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'booking_id',
                'booking_check_out_id',
                'cleaning_type',
                'reason',
                'status',
                'priority',
                'scheduled_date',
                'scheduled_time',
                'due_at',
                'has_damage_found',
                'has_forgotten_items',
                'needs_repair',
                'place_ready_after_cleaning',
            ])
            ->where('user_id', $host->id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['cleaning_type'] ?? null, fn ($query, $type) => $query->where('cleaning_type', $type))
            ->when($filters['property_id'] ?? null, fn ($query, $id) => $query->where('property_id', $id))
            ->when($filters['room_id'] ?? null, fn ($query, $id) => $query->where('room_id', $id))
            ->when($filters['sleeping_place_id'] ?? null, fn ($query, $id) => $query->where('sleeping_place_id', $id))
            ->when(($filters['today'] ?? false) === true, fn ($query) => $query->whereDate('scheduled_date', now()->toDateString()))
            ->when(($filters['overdue'] ?? false) === true, fn ($query) => $query->where('status', 'overdue'))
            ->when(($filters['needs_repair'] ?? false) === true, fn ($query) => $query->where('needs_repair', true))
            ->orderBy('scheduled_date')
            ->orderBy('id')
            ->cursorPaginate(10);
    }

    public function summary(User $host): array
    {
        $base = HostCleaningTask::query()->where('user_id', $host->id);

        return [
            'today' => (clone $base)->whereDate('scheduled_date', now()->toDateString())->count(),
            'overdue' => (clone $base)->where('status', 'overdue')->count(),
            'after_check_out' => (clone $base)->where('cleaning_type', 'after_check_out')->count(),
            'before_check_in' => (clone $base)->where('cleaning_type', 'before_check_in')->count(),
        ];
    }
}
