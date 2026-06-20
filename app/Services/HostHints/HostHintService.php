<?php

namespace App\Services\HostHints;

use App\Models\HostHintAction;
use App\Models\HostHintDismissal;
use App\Models\HostHintSnapshot;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HostHintService
{
    public function __construct(
        private readonly HostPhotoHintService $photos,
        private readonly HostDescriptionHintService $descriptions,
        private readonly HostPricingHintService $pricing,
        private readonly HostRulesHintService $rules,
        private readonly HostCalendarHintService $calendar,
        private readonly HostSafetyHintService $safety,
        private readonly HostAccessHintService $access,
        private readonly HostHintPriorityService $priority,
        private readonly HostHintDismissalService $dismissals,
    ) {}

    /**
     * @return Collection<int, HostHintSnapshot>
     */
    public function getHintsForWizard(User $host, Property|Room|SleepingPlace $target, ?string $step = null): Collection
    {
        $this->refreshTargetIfNeeded($target);

        $query = $this->baseQuery($host, $target)->where('show_in_wizard', true);
        if (filled($step)) {
            $query->whereIn('category', $this->categoriesForStep($step));
        }

        return $this->visibleForContext($host, $query->get(), 'wizard')
            ->pipe(fn (Collection $hints): Collection => $this->priority->limitForWizard($hints));
    }

    /**
     * @return Collection<int, HostHintSnapshot>
     */
    public function getHintsForDashboard(User $host): Collection
    {
        $hints = HostHintSnapshot::query()
            ->active()
            ->fresh()
            ->where('user_id', $host->id)
            ->where('show_in_dashboard', true)
            ->orderByDesc('priority')
            ->limit(60)
            ->get();

        return $this->visibleForContext($host, $hints, 'dashboard')
            ->pipe(fn (Collection $items): Collection => $this->priority->sortByImportanceAndContext($items, 'dashboard'));
    }

    /**
     * @return Collection<int, HostHintSnapshot>
     */
    public function getHintsBeforePublish(User $host, SleepingPlace $place): Collection
    {
        $this->refreshHintsForSleepingPlace($place);

        $hints = $this->baseQuery($host, $place)
            ->where('show_before_publish', true)
            ->get();

        return $this->visibleForContext($host, $hints, 'before_publish')
            ->pipe(fn (Collection $items): Collection => $this->priority->showCriticalFirstBeforePublish($items));
    }

    public function refreshHintsForProperty(Property $property): int
    {
        $property->loadMissing(['rooms.sleepingPlaces', 'sleepingPlaces']);
        $count = 0;

        foreach ($property->sleepingPlaces as $place) {
            $count += $this->refreshHintsForSleepingPlace($place);
        }

        return $count;
    }

    public function refreshHintsForRoom(Room $room): int
    {
        $room->loadMissing('sleepingPlaces');
        $count = 0;

        foreach ($room->sleepingPlaces as $place) {
            $count += $this->refreshHintsForSleepingPlace($place);
        }

        return $count;
    }

    public function refreshHintsForSleepingPlace(SleepingPlace $place): int
    {
        $place = $place->fresh([
            'property.host.hostProfile',
            'property.translations',
            'property.accessDetails',
            'room',
            'translations',
        ]) ?? $place;

        $host = $place->property?->host;

        if (! $host instanceof User) {
            return 0;
        }

        HostHintSnapshot::query()
            ->where('user_id', $host->id)
            ->where('sleeping_place_id', $place->id)
            ->whereNotIn('status', ['completed', 'auto_closed'])
            ->delete();

        $payloads = collect()
            ->merge($this->photos->forSleepingPlace($place))
            ->merge($this->descriptions->forSleepingPlace($place))
            ->merge($this->pricing->forSleepingPlace($place))
            ->merge($this->rules->forSleepingPlace($place))
            ->merge($this->calendar->forSleepingPlace($place))
            ->merge($this->safety->forSleepingPlace($place))
            ->merge($this->access->forSleepingPlace($place))
            ->unique('hint_key')
            ->values();

        $payloads->each(function (array $payload) use ($host, $place): void {
            HostHintSnapshot::query()->create(array_merge($payload, [
                'user_id' => $host->id,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'sleeping_place_id' => $place->id,
            ]));
        });

        return $payloads->count();
    }

    public function markAsCompleted(HostHintSnapshot $hint): HostHintSnapshot
    {
        $hint->forceFill(['status' => 'completed'])->save();

        HostHintAction::query()->create([
            'user_id' => $hint->user_id,
            'host_hint_snapshot_id' => $hint->id,
            'action' => 'completed',
            'action_status' => 'done',
            'acted_at' => now(),
        ]);

        return $hint->refresh();
    }

    public function dismiss(User $host, HostHintSnapshot $hint, ?Carbon $until = null): HostHintDismissal
    {
        return $this->dismissals->dismiss($host, $hint, $until);
    }

    /**
     * @param  Collection<int, HostHintSnapshot>  $hints
     * @return Collection<int, HostHintSnapshot>
     */
    private function visibleForContext(User $host, Collection $hints, string $context): Collection
    {
        return $hints
            ->reject(fn (HostHintSnapshot $hint): bool => $this->dismissals->isDismissed($host, $hint, $context))
            ->values();
    }

    private function baseQuery(User $host, Property|Room|SleepingPlace $target)
    {
        $query = HostHintSnapshot::query()
            ->active()
            ->fresh()
            ->where('user_id', $host->id);

        if ($target instanceof SleepingPlace) {
            return $query->where('sleeping_place_id', $target->id);
        }

        if ($target instanceof Room) {
            return $query->where('room_id', $target->id);
        }

        return $query->where('property_id', $target->id);
    }

    private function refreshTargetIfNeeded(Property|Room|SleepingPlace $target): void
    {
        if ($target instanceof SleepingPlace) {
            $this->refreshHintsForSleepingPlace($target);

            return;
        }

        if ($target instanceof Room) {
            $this->refreshHintsForRoom($target);

            return;
        }

        $this->refreshHintsForProperty($target);
    }

    /**
     * @return list<string>
     */
    private function categoriesForStep(string $step): array
    {
        return match ($step) {
            'photo', 'photos', 'media' => ['photos'],
            'rule', 'rules' => ['rules', 'room'],
            'price', 'pricing' => ['pricing'],
            'access', 'keys' => ['access'],
            'safety' => ['safety'],
            'calendar', 'availability' => ['calendar'],
            'description' => ['description'],
            default => ['photos', 'description', 'pricing', 'rules', 'calendar', 'access', 'safety', 'room', 'sleeping_place'],
        };
    }
}
