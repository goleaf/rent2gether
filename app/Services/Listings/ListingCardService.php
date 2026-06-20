<?php

namespace App\Services\Listings;

use App\Data\Listings\ListingCardContext;
use App\Data\Listings\ListingCardData;
use App\Data\Occupants\DateRange;
use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Services\Compatibility\CompatibilityCalculatorService;
use App\Services\AvailabilityService;
use App\Services\CompatibilityService;
use BackedEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ListingCardService
{
    /**
     * @var array<int, User|null>
     */
    private array $compatibilityUsers = [];

    public function __construct(
        private readonly ListingCardPriceService $prices,
        private readonly ListingCardBadgeService $badges,
        private readonly ListingCardOccupancyService $occupancy,
        private readonly ListingCardAmenityRuleService $amenitiesAndRules,
        private readonly ListingCardPrivacyService $privacy,
        private readonly AvailabilityService $availability,
        private readonly CompatibilityService $compatibility,
        private readonly CompatibilityCalculatorService $compatibilityCalculator,
    ) {}

    public function build(SleepingPlace $place, ListingCardContext $context): ListingCardData
    {
        $favoriteIds = $this->favoriteIds([$place->id], $context);
        $waitlistIds = $this->waitlistIds([$place->id], $context);
        $comparisonIds = $this->comparisonIds($context);

        return $this->buildOne($place, $context, $favoriteIds, $waitlistIds, $comparisonIds);
    }

    /**
     * @param  Collection<int, SleepingPlace>  $places
     * @return Collection<int, ListingCardData>
     */
    public function buildMany(Collection $places, ListingCardContext $context): Collection
    {
        $ids = $places->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        $favoriteIds = $this->favoriteIds($ids, $context);
        $waitlistIds = $this->waitlistIds($ids, $context);
        $comparisonIds = $this->comparisonIds($context);

        return $places
            ->map(fn (SleepingPlace $place): ListingCardData => $this->buildOne($place, $context, $favoriteIds, $waitlistIds, $comparisonIds))
            ->values();
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, ListingCardData>
     */
    public function buildFromIds(array $ids, ListingCardContext $context): Collection
    {
        $places = app(ListingCardQueryService::class)
            ->forComparison($ids, $context)
            ->get()
            ->sortBy(fn (SleepingPlace $place): int => array_search($place->id, $ids, true))
            ->values();

        return $this->buildMany($places, $context);
    }

    /**
     * @param  list<int>  $favoriteIds
     * @param  list<int>  $waitlistIds
     * @param  list<int>  $comparisonIds
     */
    private function buildOne(
        SleepingPlace $place,
        ListingCardContext $context,
        array $favoriteIds,
        array $waitlistIds,
        array $comparisonIds,
    ): ListingCardData {
        $title = $this->privacy->title($place, $context->locale);
        $media = $this->privacy->primaryMedia($place);
        $price = $this->prices->getPriceForCard($place, $context);
        $occupancy = $this->occupancy->getOccupancy($place);
        $hostProfile = $place->property?->host?->hostProfile;
        $hostRating = $hostProfile?->rating_average ?: $place->property?->host?->rating_as_host;
        $hostVerified = $hostProfile?->verified_at !== null || (bool) $place->property?->host?->identity_verified;
        [$isAvailable, $availabilityStatus, $availabilityMessage] = $this->availabilityState($place, $context);
        $keyAmenities = $this->amenitiesAndRules->keyAmenities($place, $context->locale);
        $keyRules = $this->amenitiesAndRules->keyRules($place, $context->locale);
        $selfCheckIn = $this->amenitiesAndRules->hasAmenity($place, ['self_check_in', 'key_safe', 'electronic_lock']);
        $compatibility = $this->compatibilityData($place, $context);

        return new ListingCardData(
            sleepingPlaceId: (int) $place->id,
            propertyId: (int) $place->property_id,
            roomId: (int) $place->room_id,
            hostUserId: (int) ($place->property?->host_user_id ?: 0),
            title: $title,
            summary: $this->privacy->summary($place, $context->locale),
            primaryPhotoUrl: $media?->imageUrl('mobile'),
            imageAlt: $this->privacy->imageAlt($media, $title),
            cityName: $this->privacy->cityName($place->property),
            district: $place->property?->district,
            location: $this->privacy->location($place->property),
            propertyType: $this->label($place->property?->type ?: $place->property?->property_type),
            roomType: $this->label($place->room?->type),
            roomGenderPolicy: $this->label($place->room?->gender_policy ?: $place->room?->gender_type),
            sleepingPlaceType: $this->label($place->type),
            pricePerNight: $price->pricePerNight,
            totalPrice: $price->totalPrice,
            currency: $price->currency,
            nightsCount: $price->nightsCount,
            calendarDaysCount: $price->calendarDaysCount,
            hasDiscount: $price->hasDiscount,
            discountAmount: $price->discountAmount,
            hasDeposit: $price->hasDeposit,
            depositAmount: $price->depositAmount,
            hasFreeCancellation: $price->hasFreeCancellation,
            roomPlacesCount: $occupancy->roomPlacesCount,
            roomAvailablePlacesCount: $occupancy->roomAvailablePlacesCount,
            roomOccupiedPlacesCount: $occupancy->roomOccupiedPlacesCount,
            peopleInRoomSummary: $occupancy->peopleInRoomSummary,
            peopleInPropertyCount: $occupancy->peopleInPropertyCount,
            ratingAverage: $this->floatAttribute($place, 'published_reviews_rating') ?: ($hostRating ? (float) $hostRating : null),
            reviewsCount: (int) ($place->published_reviews_count ?? $hostProfile?->reviews_count ?? 0),
            cleanlinessRating: $this->floatAttribute($place, 'published_cleanliness_rating'),
            safetyRating: $this->floatAttribute($place, 'published_safety_rating'),
            hostRating: $hostRating ? (float) $hostRating : null,
            hostVerified: $hostVerified,
            instantBookingEnabled: (bool) $place->instant_booking_enabled,
            canExtend: (bool) $place->extensions_allowed,
            selfCheckIn: $selfCheckIn,
            keyAmenities: $keyAmenities,
            keyRules: $keyRules,
            isAvailable: $isAvailable,
            availabilityStatus: $availabilityStatus,
            availabilityMessage: $availabilityMessage,
            isFavorited: in_array((int) $place->id, $favoriteIds, true),
            isInComparison: in_array((int) $place->id, $comparisonIds, true),
            isInWaitlist: in_array((int) $place->id, $waitlistIds, true),
            fitStatus: $compatibility['fit_status'],
            compatibilityScore: $compatibility['score'],
            badges: $this->badges->badges($place, $price, $occupancy, $isAvailable, $hostVerified, $selfCheckIn),
            warnings: $compatibility['warnings'],
            url: route('places.show', array_filter([
                'locale' => $context->locale,
                'sleepingPlace' => $place,
                'in' => $context->checkInDate,
                'out' => $context->checkOutDate,
                'guests' => $context->guestsCount,
            ])),
            bookUrl: route('places.book', [
                'locale' => $context->locale,
                'sleepingPlace' => $place,
            ]),
            checkInDate: $context->checkInDate,
            checkOutDate: $context->checkOutDate,
            guestsCount: max(1, $context->guestsCount),
            variant: (string) ($context->filters['variant'] ?? 'search'),
        );
    }

    /**
     * @return array{0:?bool,1:string,2:string}
     */
    private function availabilityState(SleepingPlace $place, ListingCardContext $context): array
    {
        if (! $context->hasDates()) {
            return [null, 'no_dates', __('listing_card.choose_dates_for_total')];
        }

        $availableById = $context->filters['availability_by_id'] ?? [];

        if (is_array($availableById) && array_key_exists($place->id, $availableById)) {
            $available = (bool) $availableById[$place->id];
        } elseif (($context->filters['search_filtered_available'] ?? false) === true) {
            $available = true;
        } else {
            $available = $this->availability->isAvailable($place, (string) $context->checkInDate, (string) $context->checkOutDate);
        }

        return $available
            ? [true, 'available', __('listing_card.available')]
            : [false, 'unavailable', __('listing_card.unavailable_dates')];
    }

    /**
     * @return array{fit_status:?string,score:?int,warnings:list<string>}
     */
    private function compatibilityData(SleepingPlace $place, ListingCardContext $context): array
    {
        if (! $context->userId) {
            return ['fit_status' => null, 'score' => null, 'warnings' => []];
        }

        $user = $this->compatibilityUser($context->userId);

        if ($user?->guestCompatibilityProfile && $context->hasDates()) {
            $result = $this->compatibilityCalculator->calculate(
                $user,
                $place,
                new DateRange((string) $context->checkInDate, (string) $context->checkOutDate),
            );

            return [
                'fit_status' => $result->fitStatus,
                'score' => $result->score,
                'warnings' => collect([...$result->blockingReasons, ...$result->warningReasons])
                    ->pluck('message')
                    ->filter()
                    ->take(2)
                    ->values()
                    ->all(),
            ];
        }

        if (! $user?->guestPreference) {
            return ['fit_status' => null, 'score' => null, 'warnings' => []];
        }

        $result = $this->compatibility->evaluate(
            $user->guestPreference,
            $place->property,
            $place->room,
            $place,
        );

        return [
            'fit_status' => $result['fit_level'] ?? null,
            'score' => isset($result['score']) ? (int) $result['score'] : null,
            'warnings' => array_slice($result['warning_reasons'] ?? [], 0, 2),
        ];
    }

    private function compatibilityUser(int $userId): ?User
    {
        if (array_key_exists($userId, $this->compatibilityUsers)) {
            return $this->compatibilityUsers[$userId];
        }

        return $this->compatibilityUsers[$userId] = User::query()
            ->with(['guestPreference', 'guestCompatibilityProfile', 'guestCompatibilityVisibilitySetting'])
            ->find($userId);
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function favoriteIds(array $ids, ListingCardContext $context): array
    {
        $providedIds = $context->filters['favorite_ids'] ?? null;

        if (is_array($providedIds)) {
            return collect($providedIds)
                ->map(fn (mixed $id): int => (int) $id)
                ->intersect($ids)
                ->values()
                ->all();
        }

        if (! $context->userId || $ids === []) {
            return [];
        }

        return Favorite::query()
            ->where('user_id', $context->userId)
            ->whereIn('sleeping_place_id', $ids)
            ->pluck('sleeping_place_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function waitlistIds(array $ids, ListingCardContext $context): array
    {
        $providedIds = $context->filters['waitlist_ids'] ?? null;

        if (is_array($providedIds)) {
            return collect($providedIds)
                ->map(fn (mixed $id): int => (int) $id)
                ->intersect($ids)
                ->values()
                ->all();
        }

        if (! $context->userId || $ids === []) {
            return [];
        }

        return WaitlistItem::query()
            ->where('user_id', $context->userId)
            ->whereIn('sleeping_place_id', $ids)
            ->whereIn('status', ['active', 'offered', 'awaiting_guest'])
            ->pluck('sleeping_place_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    private function comparisonIds(ListingCardContext $context): array
    {
        $ids = $context->filters['comparison_ids'] ?? session('comparison_places', []);

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        return collect(is_array($ids) ? $ids : [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    private function label(mixed $value): string
    {
        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        if ($value === null || $value === '') {
            return __('listing_card.not_set');
        }

        $key = 'listing_card.values.'.Str::slug((string) $value, '_');

        return Lang::has($key) ? __($key) : (string) $value;
    }

    private function floatAttribute(SleepingPlace $place, string $attribute): ?float
    {
        $value = $place->getAttribute($attribute);

        return $value === null ? null : (float) $value;
    }
}
