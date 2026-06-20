<?php

namespace App\Services\Compatibility;

use App\Data\Compatibility\CompatibilityContext;
use App\Data\Compatibility\CompatibilityReasonData;
use App\Data\Compatibility\CompatibilityResultData;
use App\Data\Occupants\DateRange;
use App\Models\CompatibilityResult;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Collection;

class CompatibilityCalculatorService
{
    public function __construct(
        private readonly GuestCompatibilityProfileService $profiles,
        private readonly CompatibilityVisibilityService $visibility,
        private readonly RoomCompatibilityProfileService $rooms,
        private readonly SleepingPlaceCompatibilityProfileService $sleepingPlaces,
        private readonly CompatibilityReasonService $reasons,
        private readonly CompatibilityCacheService $cache,
    ) {}

    public function calculate(User $guest, SleepingPlace $place, DateRange $range): CompatibilityResultData
    {
        if (! $this->visibility->canUseProfileForMatching($guest)) {
            return new CompatibilityResultData(
                score: 50,
                fitStatus: 'attention',
                warningReasons: [new CompatibilityReasonData('matching_disabled', __('compatibility.warnings.matching_disabled'), 0, 'warning')],
            );
        }

        $cached = $this->cache->getCached($guest, $place, $range);
        if ($cached instanceof CompatibilityResult) {
            return $this->fromCached($cached);
        }

        $context = $this->context($guest, $place, $range);
        $positive = $this->reasons->buildPositiveReasons($context);
        $warnings = $this->reasons->buildWarningReasons($context);
        $blocking = $this->reasons->buildBlockingReasons($context);
        $score = $this->score($positive, $warnings, $blocking);
        $result = new CompatibilityResultData(
            score: $score,
            fitStatus: $this->getFitStatus($score, $blocking),
            positiveReasons: array_slice($positive, 0, 8),
            warningReasons: array_slice($warnings, 0, 8),
            blockingReasons: $blocking,
        );

        $this->cache->store($guest, $place, $range, $result);

        return $result;
    }

    public function calculateForRoom(User $guest, Room $room, DateRange $range): CompatibilityResultData
    {
        $place = $room->sleepingPlaces()
            ->select(['id', 'property_id', 'room_id', 'type', 'sleeping_place_type', 'status'])
            ->first();

        if (! $place instanceof SleepingPlace) {
            return new CompatibilityResultData(0, 'not_suitable', blockingReasons: [
                new CompatibilityReasonData('room_has_no_sleeping_place', __('compatibility.blocking.room_has_no_sleeping_place'), 100, 'blocking'),
            ]);
        }

        return $this->calculate($guest, $place, $range);
    }

    /**
     * @param  Collection<int, SleepingPlace>  $places
     * @return Collection<int, CompatibilityResultData>
     */
    public function calculateForSearchResults(User $guest, Collection $places, DateRange $range): Collection
    {
        return $places
            ->map(fn (SleepingPlace $place): CompatibilityResultData => $this->calculate($guest, $place, $range))
            ->values();
    }

    /**
     * @param  list<CompatibilityReasonData|array<string, mixed>>  $blockingReasons
     */
    public function getFitStatus(int $score, array $blockingReasons): string
    {
        if ($blockingReasons !== []) {
            return 'not_suitable';
        }

        return match (true) {
            $score >= 85 => 'great',
            $score >= 70 => 'good',
            $score >= 50 => 'attention',
            $score >= 30 => 'uncomfortable',
            default => 'not_suitable',
        };
    }

    /**
     * @return list<CompatibilityReasonData>
     */
    public function getPositiveReasons(User $guest, SleepingPlace $place, DateRange $range): array
    {
        return $this->calculate($guest, $place, $range)->positiveReasons;
    }

    /**
     * @return list<CompatibilityReasonData>
     */
    public function getWarningReasons(User $guest, SleepingPlace $place, DateRange $range): array
    {
        return $this->calculate($guest, $place, $range)->warningReasons;
    }

    /**
     * @return list<CompatibilityReasonData>
     */
    public function getBlockingReasons(User $guest, SleepingPlace $place, DateRange $range): array
    {
        return $this->calculate($guest, $place, $range)->blockingReasons;
    }

    private function context(User $guest, SleepingPlace $place, DateRange $range): CompatibilityContext
    {
        $place->loadMissing(['room.property', 'compatibilityProfile', 'room.compatibilityProfile']);
        $room = $place->room;

        return new CompatibilityContext(
            guest: $guest,
            guestProfile: $this->profiles->getProfile($guest),
            room: $room,
            sleepingPlace: $place,
            roomProfile: $room->compatibilityProfile ?: $this->rooms->syncFromRoom($room),
            sleepingPlaceProfile: $place->compatibilityProfile ?: $this->sleepingPlaces->syncFromSleepingPlace($place),
            range: $range,
            propertyAmenities: $this->values($place->property, 'amenities'),
            propertyRules: $this->values($place->property, 'rules'),
        );
    }

    /**
     * @param  list<CompatibilityReasonData>  $positive
     * @param  list<CompatibilityReasonData>  $warnings
     * @param  list<CompatibilityReasonData>  $blocking
     */
    private function score(array $positive, array $warnings, array $blocking): int
    {
        $points = collect($positive)->sum(fn (CompatibilityReasonData $reason): int => $reason->weight);
        $penalty = collect($warnings)->sum(fn (CompatibilityReasonData $reason): int => $reason->weight);
        $blockingPenalty = $blocking === [] ? 0 : 50;

        return max(0, min(100, 100 + $points - $penalty - $blockingPenalty));
    }

    private function fromCached(CompatibilityResult $result): CompatibilityResultData
    {
        return new CompatibilityResultData(
            score: (int) $result->compatibility_score,
            fitStatus: $result->fit_status,
            positiveReasons: $this->reasonData($result->positive_reasons_json ?? []),
            warningReasons: $this->reasonData($result->warning_reasons_json ?? []),
            blockingReasons: $this->reasonData($result->blocking_reasons_json ?? []),
        );
    }

    /**
     * @param  list<array{key?:string,message?:string,weight?:int,severity?:string}>  $items
     * @return list<CompatibilityReasonData>
     */
    private function reasonData(array $items): array
    {
        return collect($items)
            ->map(fn (array $item): CompatibilityReasonData => new CompatibilityReasonData(
                key: (string) ($item['key'] ?? 'unknown'),
                message: (string) ($item['message'] ?? ''),
                weight: (int) ($item['weight'] ?? 0),
                severity: (string) ($item['severity'] ?? 'info'),
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function values(mixed $model, string $attribute): array
    {
        $value = $model?->getAttribute($attribute);

        if ($value instanceof Collection) {
            return $value
                ->pluck('slug')
                ->merge($value->pluck('name_normalized'))
                ->filter()
                ->map(fn (mixed $item): string => str((string) $item)->lower()->replace(' ', '_')->toString())
                ->values()
                ->all();
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function (mixed $item): string {
                if ($item instanceof BackedEnum) {
                    return $item->value;
                }

                return str((string) $item)->lower()->replace(' ', '_')->toString();
            })
            ->values()
            ->all();
    }
}
