<?php

namespace App\Services\HostCalendar;

use App\Enums\AvailabilityStatus;
use App\Models\HostCalendarEvent;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HostCalendarRepairService
{
    public function createRepairEvent(User $host, Property|Room|SleepingPlace $target, array $range, string $note): HostCalendarEvent
    {
        [$property, $room, $place] = $this->resolveTarget($host, $target);

        if ($place instanceof SleepingPlace) {
            $this->blockCalendarForRepair($place, $range);
        }

        return HostCalendarEvent::query()->create([
            'user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room?->id,
            'sleeping_place_id' => $place?->id,
            'event_type' => 'repair',
            'event_status' => 'planned',
            'event_date' => CarbonImmutable::parse($range['start'])->toDateString(),
            'title_key' => 'host_calendar.event_titles.repair',
            'description_key' => 'host_calendar.event_descriptions.repair',
            'place_status' => 'repair',
            'needs_repair' => true,
            'priority' => 85,
            'source' => 'repair',
            'host_note' => $note,
            'is_private' => true,
        ]);
    }

    public function markRepairDone(HostCalendarEvent $event): HostCalendarEvent
    {
        $event->forceFill([
            'event_status' => 'done',
            'needs_repair' => false,
            'place_status' => 'available',
        ])->save();

        return $event->refresh();
    }

    public function getRepairEvents(User $host, array $range): Collection
    {
        return HostCalendarEvent::query()
            ->where('user_id', $host->id)
            ->where('event_type', 'repair')
            ->whereDate('event_date', '>=', $range['start'])
            ->whereDate('event_date', '<', $range['end'])
            ->orderBy('event_date')
            ->get();
    }

    public function blockCalendarForRepair(SleepingPlace $place, array $range): void
    {
        foreach (CarbonPeriod::create(CarbonImmutable::parse($range['start']), CarbonImmutable::parse($range['end'])->subDay()) as $date) {
            $dateString = $date->toDateString();
            $place->calendarDays()->updateOrCreate(
                ['date' => $dateString],
                [
                    'status' => 'repair',
                    'currency' => $place->currency,
                    'check_in_allowed' => false,
                    'check_out_allowed' => false,
                    'reason' => 'repair',
                    'source' => 'repair',
                    'blocked_by_host' => true,
                ],
            );
            $place->availabilityDays()->updateOrCreate(
                ['date' => $dateString],
                [
                    'status' => AvailabilityStatus::Repair->value,
                    'check_in_allowed' => false,
                    'check_out_allowed' => false,
                    'note' => 'repair',
                ],
            );
        }
    }

    /**
     * @return array{0:Property,1:?Room,2:?SleepingPlace}
     */
    private function resolveTarget(User $host, Property|Room|SleepingPlace $target): array
    {
        if ($target instanceof Property) {
            if (! $target->isOwnedBy($host)) {
                throw new AuthorizationException;
            }

            return [$target, null, null];
        }

        if ($target instanceof Room) {
            $target->loadMissing('property:id,host_user_id,user_id');

            if (! $target->property?->isOwnedBy($host)) {
                throw new AuthorizationException;
            }

            return [$target->property, $target, null];
        }

        $target->loadMissing(['property:id,host_user_id,user_id', 'room:id,property_id']);

        if (! $target->property?->isOwnedBy($host)) {
            throw new AuthorizationException;
        }

        return [$target->property, $target->room, $target];
    }
}
