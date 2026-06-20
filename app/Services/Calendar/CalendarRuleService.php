<?php

namespace App\Services\Calendar;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarRule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;

class CalendarRuleService
{
    public function createRule(User $host, SleepingPlace $place, array $data): SleepingPlaceCalendarRule
    {
        $this->authorizePlace($host, $place);

        return $place->calendarRules()->create($this->payload($data));
    }

    public function updateRule(User $host, SleepingPlaceCalendarRule $rule, array $data): SleepingPlaceCalendarRule
    {
        $rule->loadMissing('sleepingPlace.property:id,host_user_id,user_id');
        $this->authorizePlace($host, $rule->sleepingPlace);

        $rule->fill($this->payload($data))->save();

        return $rule->refresh();
    }

    public function deleteRule(User $host, SleepingPlaceCalendarRule $rule): void
    {
        $rule->loadMissing('sleepingPlace.property:id,host_user_id,user_id');
        $this->authorizePlace($host, $rule->sleepingPlace);
        $rule->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resolveRulesForDate(SleepingPlace $place, CarbonInterface|string $date): array
    {
        $day = $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();

        return $place->calendarRules()
            ->where(function ($query) use ($day): void {
                $query->whereNull('starts_at')
                    ->orWhereDate('starts_at', '<=', $day->toDateString());
            })
            ->where(function ($query) use ($day): void {
                $query->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $day->toDateString());
            })
            ->orderByDesc('priority')
            ->get()
            ->filter(function (SleepingPlaceCalendarRule $rule) use ($day): bool {
                $weekdays = $rule->weekdays_json;

                return empty($weekdays) || in_array($day->isoWeekday(), $weekdays, true);
            })
            ->map(fn (SleepingPlaceCalendarRule $rule): array => $rule->toArray())
            ->values()
            ->all();
    }

    private function payload(array $data): array
    {
        return array_intersect_key($data, array_flip((new SleepingPlaceCalendarRule)->getFillable()));
    }

    private function authorizePlace(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,host_user_id,user_id');

        if (! $place->property?->isOwnedBy($host)) {
            throw new AuthorizationException;
        }
    }
}
