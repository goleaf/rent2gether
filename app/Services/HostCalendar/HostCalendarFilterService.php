<?php

namespace App\Services\HostCalendar;

use App\Services\HostCalendar\Data\HostCalendarFilters;
use Illuminate\Database\Eloquent\Builder;

class HostCalendarFilterService
{
    public function apply(Builder $query, HostCalendarFilters $filters): Builder
    {
        return $query
            ->when($filters->propertyId, fn (Builder $builder): Builder => $builder->where('property_id', $filters->propertyId))
            ->when($filters->roomId, fn (Builder $builder): Builder => $builder->where('room_id', $filters->roomId))
            ->when($filters->sleepingPlaceId, fn (Builder $builder): Builder => $builder->where('sleeping_place_id', $filters->sleepingPlaceId))
            ->when($filters->eventTypes !== [], fn (Builder $builder): Builder => $builder->whereIn('event_type', $filters->eventTypes))
            ->when($filters->eventStatus, fn (Builder $builder): Builder => $builder->where('event_status', $filters->eventStatus))
            ->when($filters->payoutStatus, fn (Builder $builder): Builder => $builder->where('payout_status', $filters->payoutStatus))
            ->when($filters->onlyProblems, function (Builder $builder): Builder {
                return $builder->where(function (Builder $problem): void {
                    $problem
                        ->where('needs_cleaning', true)
                        ->orWhere('needs_inspection', true)
                        ->orWhere('needs_repair', true)
                        ->orWhereIn('event_status', ['overdue', 'blocked', 'failed']);
                });
            });
    }
}
