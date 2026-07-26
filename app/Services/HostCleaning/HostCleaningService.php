<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
        $host = User::query()
            ->select(['id'])
            ->withCount([
                'hostCleaningTasks as host_cleaning_today_count' => fn (Builder $query) => $query->whereDate('scheduled_date', now()->toDateString()),
                'hostCleaningTasks as host_cleaning_overdue_count' => fn (Builder $query) => $query->where('status', 'overdue'),
                'hostCleaningTasks as host_cleaning_after_check_out_count' => fn (Builder $query) => $query->where('cleaning_type', 'after_check_out'),
                'hostCleaningTasks as host_cleaning_before_check_in_count' => fn (Builder $query) => $query->where('cleaning_type', 'before_check_in'),
            ])
            ->find($host->id);

        if (! $host instanceof User) {
            return [
                'today' => 0,
                'overdue' => 0,
                'after_check_out' => 0,
                'before_check_in' => 0,
            ];
        }

        return [
            'today' => (int) $host->host_cleaning_today_count,
            'overdue' => (int) $host->host_cleaning_overdue_count,
            'after_check_out' => (int) $host->host_cleaning_after_check_out_count,
            'before_check_in' => (int) $host->host_cleaning_before_check_in_count,
        ];
    }
}
