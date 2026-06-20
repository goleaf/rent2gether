<?php

namespace App\Services\SavedSearches;

use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SavedSearchMatcherService
{
    private const MATCH_LIMIT = 50;

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function findMatches(SavedSearch $search): Collection
    {
        return $this->buildQueryFromSavedSearch($search)
            ->limit(self::MATCH_LIMIT)
            ->get();
    }

    /**
     * @return Builder<SleepingPlace>
     */
    public function buildQueryFromSavedSearch(SavedSearch $search): Builder
    {
        $query = SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'status',
                'place_number',
                'display_name',
                'bunk_level',
                'has_locker',
                'has_bedding',
                'has_towel',
                'max_guests',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'instant_booking_enabled',
            ])
            ->active()
            ->with([
                'property:id,host_user_id,city_id,district,status,type,kitchens_count,has_elevator,has_parking',
                'property.host:id,identity_verified,rating_as_host',
                'property.host.hostProfile:id,user_id,verified_at,rating_average,reviews_count',
                'property.amenities:id,slug',
                'room:id,property_id,status,type,gender_policy,max_guests,beds_count,available_places_count,has_desk',
                'amenities:id,slug',
                'translations:id,sleeping_place_id,locale,title,summary',
                'cardMedia:id,mediable_id,mediable_type,disk,path,alt_text,is_primary,is_cover,sort_order,conversions',
            ]);

        return $this->filterByInstantBooking(
            $this->filterByTrust(
                $this->filterByExcludedConditions(
                    $this->filterByAmenities(
                        $this->filterBySleepingPlaceType(
                            $this->filterByRoomType(
                                $this->filterByBudget(
                                    $this->filterByDates(
                                        $this->filterByCityAndDistrict($query, $search),
                                        $search,
                                    ),
                                    $search,
                                ),
                                $search,
                            ),
                            $search,
                        ),
                        $search,
                    ),
                    $search,
                ),
                $search,
            ),
            $search,
        )->orderBy('base_price_per_night')->orderBy('id');
    }

    public function calculateMatchScore(SavedSearch $search, SleepingPlace $place): int
    {
        $score = 70;

        if ($search->budget_max !== null && (float) $place->base_price_per_night <= (float) $search->budget_max) {
            $score += 10;
        }

        if ($search->only_instant_booking && $place->instant_booking_enabled) {
            $score += 5;
        }

        if ($search->require_locker && $place->has_locker) {
            $score += 5;
        }

        if ($search->require_workspace && $place->room?->has_desk) {
            $score += 5;
        }

        if ($search->only_verified_hosts && $place->property?->host?->identity_verified) {
            $score += 5;
        }

        return max(0, min(100, $score));
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByDates(Builder $query, SavedSearch $search): Builder
    {
        $checkIn = $search->check_in_date ?: $search->check_in;
        $checkOut = $search->check_out_date ?: $search->check_out;

        if (! $checkIn || ! $checkOut) {
            return $query;
        }

        return $query->availableBetween((string) $checkIn, (string) $checkOut);
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByBudget(Builder $query, SavedSearch $search): Builder
    {
        $min = $search->budget_min ?? $search->price_min;
        $max = $search->budget_max ?? $search->price_max;

        if ($min !== null) {
            $query->where('base_price_per_night', '>=', $min);
        }

        if ($max !== null) {
            $query->where('base_price_per_night', '<=', $max);
        }

        if ($search->no_deposit_only) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('deposit_amount')->orWhere('deposit_amount', '<=', 0);
            });
        }

        if ($search->max_deposit !== null) {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->whereNull('deposit_amount')->orWhere('deposit_amount', '<=', $search->max_deposit);
            });
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByRoomType(Builder $query, SavedSearch $search): Builder
    {
        $roomTypes = array_filter((array) ($search->room_types_json ?: []));

        if ($search->room_type) {
            $roomTypes[] = $search->room_type;
        }

        if ($roomTypes !== []) {
            $query->whereHas('room', fn (Builder $room) => $room->whereIn('type', array_values(array_unique($roomTypes))));
        }

        if ($search->room_gender_policy) {
            $query->whereHas('room', fn (Builder $room) => $room->where('gender_policy', $search->room_gender_policy));
        }

        if ($search->avoid_mixed_room) {
            $query->whereHas('room', fn (Builder $room) => $room->where('gender_policy', '!=', 'mixed'));
        }

        if ($search->max_people_in_room !== null) {
            $query->whereHas('room', fn (Builder $room) => $room->where('max_guests', '<=', $search->max_people_in_room));
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterBySleepingPlaceType(Builder $query, SavedSearch $search): Builder
    {
        $types = array_filter((array) ($search->sleeping_place_types_json ?: []));

        if ($search->bed_type) {
            $types[] = $search->bed_type;
        }

        if ($types !== []) {
            $query->whereIn('type', array_values(array_unique($types)));
        }

        if ($search->lower_bunk_only || $search->exclude_upper_bunk) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('bunk_level')->orWhere('bunk_level', '<=', 1);
            });
        }

        if ($search->exclude_sofa) {
            $query->where('type', '!=', 'sofa');
        }

        if ($search->exclude_mattress) {
            $query->where('type', '!=', 'floor_mattress');
        }

        if ($search->require_locker) {
            $query->where('has_locker', true);
        }

        if ($search->require_workspace) {
            $query->whereHas('room', fn (Builder $room) => $room->where('has_desk', true));
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByAmenities(Builder $query, SavedSearch $search): Builder
    {
        foreach ((array) ($search->required_amenity_ids_json ?: []) as $amenityId) {
            $this->whereHasAmenity($query, (int) $amenityId);
        }

        foreach ($this->requiredAmenitySlugs($search) as $slug) {
            $this->whereHasAmenitySlug($query, $slug);
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByExcludedConditions(Builder $query, SavedSearch $search): Builder
    {
        if ($search->avoid_pets) {
            $query->whereHas('property', function (Builder $property): void {
                $property->where(function (Builder $builder): void {
                    $builder->whereNull('rules')->orWhereJsonDoesntContain('rules', 'pets_allowed');
                });
            });
        }

        if ($search->avoid_smoking) {
            $query->whereHas('property', function (Builder $property): void {
                $property->where(function (Builder $builder): void {
                    $builder->whereNull('rules')->orWhereJsonDoesntContain('rules', 'smoking_allowed');
                });
            });
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByTrust(Builder $query, SavedSearch $search): Builder
    {
        if ($search->only_verified_hosts) {
            $query->whereHas('property.host', fn (Builder $host) => $host->where('identity_verified', true));
        }

        if ($search->only_with_reviews) {
            $query->whereHas('property.host.hostProfile', fn (Builder $profile) => $profile->where('reviews_count', '>', 0));
        }

        if ($search->min_host_rating !== null || $search->min_rating !== null) {
            $rating = $search->min_host_rating ?? $search->min_rating;
            $query->whereHas('property.host.hostProfile', fn (Builder $profile) => $profile->where('rating_average', '>=', $rating));
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    public function filterByInstantBooking(Builder $query, SavedSearch $search): Builder
    {
        if ($search->only_instant_booking) {
            $query->where('instant_booking_enabled', true);
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     * @return Builder<SleepingPlace>
     */
    private function filterByCityAndDistrict(Builder $query, SavedSearch $search): Builder
    {
        if ($search->city_id) {
            $query->whereHas('property', fn (Builder $property) => $property->where('city_id', $search->city_id));
        }

        if ($search->district) {
            $query->whereHas('property', fn (Builder $property) => $property->where('district', $search->district));
        }

        return $query;
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     */
    private function whereHasAmenity(Builder $query, int $amenityId): void
    {
        $query->where(function (Builder $builder) use ($amenityId): void {
            $builder->whereHas('amenities', fn (Builder $amenity) => $amenity->whereKey($amenityId))
                ->orWhereHas('room.amenities', fn (Builder $amenity) => $amenity->whereKey($amenityId))
                ->orWhereHas('property.amenities', fn (Builder $amenity) => $amenity->whereKey($amenityId));
        });
    }

    /**
     * @param  Builder<SleepingPlace>  $query
     */
    private function whereHasAmenitySlug(Builder $query, string $slug): void
    {
        $query->where(function (Builder $builder) use ($slug): void {
            $builder->whereHas('amenities', fn (Builder $amenity) => $amenity->where('slug', $slug))
                ->orWhereHas('room.amenities', fn (Builder $amenity) => $amenity->where('slug', $slug))
                ->orWhereHas('property.amenities', fn (Builder $amenity) => $amenity->where('slug', $slug));
        });
    }

    /**
     * @return list<string>
     */
    private function requiredAmenitySlugs(SavedSearch $search): array
    {
        $slugs = [];

        if ($search->require_wifi) {
            $slugs[] = 'wifi';
        }

        if ($search->require_kitchen) {
            $slugs[] = 'kitchen';
        }

        if ($search->require_washing_machine) {
            $slugs[] = 'washing_machine';
        }

        return $slugs;
    }
}
