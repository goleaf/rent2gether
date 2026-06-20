<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Hints\HintContext;
use App\Models\City;
use App\Models\ListingHintSnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Collection;

class ListingHintCalculatorService
{
    public function __construct(
        private readonly PriceHintService $prices,
        private readonly AvailabilityHintService $availability,
        private readonly TrustHintService $trust,
        private readonly HostHintService $hostHints,
        private readonly RoomOccupancyHintService $occupancy,
        private readonly RulesHintService $rules,
        private readonly SafetyHintService $safety,
        private readonly CompatibilityHintService $compatibility,
        private readonly CancellationHintService $cancellation,
        private readonly HintPriorityService $priority,
    ) {}

    /**
     * @return Collection<int, GuestHintData>
     */
    public function calculateForSleepingPlace(SleepingPlace $place): Collection
    {
        return $this->calculateStaticHints($place);
    }

    public function refreshSnapshots(SleepingPlace $place): int
    {
        $hints = $this->calculateStaticHints($place);

        ListingHintSnapshot::query()
            ->where('sleeping_place_id', $place->id)
            ->delete();

        $hints->each(function (GuestHintData $hint) use ($place): void {
            ListingHintSnapshot::query()->create([
                'sleeping_place_id' => $place->id,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'city_id' => $place->property?->city_id,
                'hint_key' => $hint->key,
                'category' => $hint->category,
                'type' => $hint->type,
                'importance' => $hint->importance,
                'priority' => $hint->priority,
                'message_key' => $hint->messageKey,
                'message_params_json' => $hint->messageParams,
                'source' => $hint->source,
                'show_on_card' => $hint->showOnCard,
                'show_on_detail' => $hint->showOnDetail,
                'show_before_booking' => $hint->showBeforeBooking,
                'show_in_favorites' => $hint->showInFavorites,
                'show_in_saved_search' => $hint->showInSavedSearch,
                'calculated_at' => now(),
                'expires_at' => now()->addHours(12),
            ]);
        });

        return $hints->count();
    }

    public function refreshForCity(City $city): int
    {
        $count = 0;

        SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id'])
            ->whereHas('property', fn ($property) => $property->where('city_id', $city->id))
            ->chunkById(100, function (Collection $places) use (&$count): void {
                $places->each(function (SleepingPlace $place) use (&$count): void {
                    $count += $this->refreshSnapshots($place);
                });
            });

        return $count;
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function calculateStaticHints(SleepingPlace $place): Collection
    {
        $this->loadPlace($place);
        $host = $this->host($place);

        $hints = collect([
            $this->trust->hasHighCleanlinessRating($place),
            $this->trust->hasHighSafetyRating($place),
            $this->trust->hasManyReviews($place),
            $this->trust->isNewListing($place),
            $this->trust->isOftenBooked($place),
            $this->trust->isOftenFavorited($place),
            $host instanceof User ? $this->hostHints->hostRespondsFast($host) : null,
            $host instanceof User ? $this->hostHints->hostVerified($host) : null,
            $host instanceof User ? $this->hostHints->hostHighRated($host) : null,
            $host instanceof User ? $this->hostHints->hostAllowsExtension($host, $place) : null,
            $this->rules->strictQuietHours($place->room),
            $this->rules->smokingForbidden($place->property, $place->room),
            $this->rules->petsForbidden($place->property),
            $this->rules->kitchenClosesAtNight($place->property),
            $this->rules->guestsForbidden($place->property),
            $this->rules->identityVerificationRequired($place),
            $this->safety->addressAfterBooking($place->property),
            $this->safety->hasPersonalLocker($place),
            $this->safety->hasEmergencyContact($place->property),
            $this->cancellation->strictCancellation($place),
            $this->availability->isInstantBooking($place),
            $this->availability->requiresHostApproval($place),
            $this->prices->hasDeposit($place),
            $this->prices->hasNoDeposit($place),
            $this->prices->hasFreeCancellation($place),
        ])->filter();

        return $this->priority->preventDuplicateSimilarHints($hints)->values();
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function calculateDynamicHints(SleepingPlace $place, HintContext $context): Collection
    {
        $this->loadPlace($place);
        $range = $context->dateRange();
        $nights = max(0, (int) ($context->nights() ?? 0));
        $user = $context->userId ? User::query()
            ->select(['id', 'name', 'rating_as_host', 'identity_verified'])
            ->with(['guestCompatibilityProfile', 'guestCompatibilityVisibilitySetting', 'hostProfile'])
            ->find($context->userId) : null;

        $hints = collect([
            $this->prices->isCheaperThanAreaAverage($place, $context),
            $this->prices->isMoreExpensiveThanSimilar($place, $context),
            $this->prices->hasWeeklyDiscount($place, $nights),
            $this->prices->hasMonthlyDiscount($place, $nights),
            $this->prices->hasDeposit($place),
            $this->prices->hasFreeCancellation($place),
            $this->availability->isInstantBooking($place),
            $this->availability->requiresHostApproval($place),
            $this->rules->strictQuietHours($place->room),
            $this->rules->smokingForbidden($place->property, $place->room),
            $this->rules->petsForbidden($place->property),
            $this->rules->identityVerificationRequired($place),
            $this->safety->addressAfterBooking($place->property),
        ])->filter();

        if ($range?->valid()) {
            $hints = $hints->merge([
                $this->prices->hasWeekendPriceChange($place, $range),
                $this->availability->hasOnePlaceLeft($place->room, $range),
                $this->availability->isAvailableForLongerStay($place, $range),
                $this->availability->canExtend($place, $range),
                $this->occupancy->getPeopleInRoomHint($place->room, $range),
                $this->occupancy->getRoomAlmostFullHint($place->room, $range),
                $this->occupancy->getQuietOccupantsHint($place->room, $range),
                $this->occupancy->getLongTermOccupantHint($place->room, $range),
            ])->filter();
        }

        if ($user instanceof User) {
            $hints = $hints
                ->push($this->compatibility->doesNotFitSelectedCriteria($user, $place, $context))
                ->merge($this->compatibility->getCompatibilityWarnings($user, $place, $context))
                ->merge($this->compatibility->getCompatibilityPositiveHints($user, $place, $context))
                ->filter();

            if ($host = $this->host($place)) {
                $hints = $hints->push($this->hostHints->hostSpeaksGuestLanguage($user, $host))->filter();
            }
        }

        return $this->priority->preventDuplicateSimilarHints($hints)->values();
    }

    private function loadPlace(SleepingPlace $place): void
    {
        $place->loadMissing([
            'property:id,host_user_id,city_id,rules,amenities,show_exact_address_before_booking,cleanliness_level,safety_level,emergency_contact_name,emergency_contact_phone',
            'property.host:id,name,rating_as_host,identity_verified',
            'property.host.hostProfile',
            'room:id,property_id,available_places_count,free_sleeping_places_count,current_guests_count,occupied_places_count,sleeping_places_count,max_guests,noise_level,rules,can_talk_at_night',
            'room.comfortDetails:id,room_id,quiet_hours_enabled',
            'rules:id,slug',
        ]);
    }

    private function host(SleepingPlace $place): ?User
    {
        $host = $place->property?->host;

        return $host instanceof User ? $host : null;
    }
}
