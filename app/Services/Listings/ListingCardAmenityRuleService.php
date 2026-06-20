<?php

namespace App\Services\Listings;

use App\Models\Amenity;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Support\Collection;

class ListingCardAmenityRuleService
{
    public const KEY_AMENITY_SLUGS = [
        'wifi',
        'fast_wifi',
        'kitchen',
        'washing_machine',
        'personal_locker',
        'locker_with_lock',
        'workspace',
        'desk',
        'self_check_in',
        'elevator',
        'parking',
    ];

    public const KEY_RULE_SLUGS = [
        'no_smoking',
        'quiet_hours',
        'quiet_hours_after_22',
        'no_parties',
        'no_pets',
        'pets_by_request',
        'no_guests',
        'late_check_in_allowed',
    ];

    public function __construct(private readonly LocalizedModelContentResolver $translations) {}

    /**
     * @return list<string>
     */
    public function keyAmenities(SleepingPlace $place, string $locale): array
    {
        $labels = $this->amenitiesForCard($place)
            ->unique('slug')
            ->filter(fn (Amenity $amenity): bool => in_array($amenity->slug, self::KEY_AMENITY_SLUGS, true))
            ->map(fn (Amenity $amenity): string => $this->amenityLabel($amenity, $locale))
            ->values();

        if ($place->has_bedding) {
            $labels->push(__('listing_card.amenities.bedding'));
        }

        if ($place->has_towel) {
            $labels->push(__('listing_card.amenities.towel'));
        }

        if ($place->has_locker && $labels->doesntContain(__('listing_card.amenities.locker'))) {
            $labels->push(__('listing_card.amenities.locker'));
        }

        return $labels->unique()->take(4)->values()->all();
    }

    /**
     * @return list<string>
     */
    public function keyRules(SleepingPlace $place, string $locale): array
    {
        return $this->rulesForCard($place)
            ->unique('slug')
            ->filter(fn (Rule $rule): bool => in_array($rule->slug, self::KEY_RULE_SLUGS, true))
            ->map(fn (Rule $rule): string => $this->ruleLabel($rule, $locale))
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    public function hasAmenity(SleepingPlace $place, array $slugs): bool
    {
        return $this->amenitiesForCard($place)
            ->pluck('slug')
            ->map(fn (string $slug): string => str($slug)->lower()->replace('-', '_')->toString())
            ->intersect($slugs)
            ->isNotEmpty();
    }

    /**
     * @return Collection<int, Amenity>
     */
    private function amenitiesForCard(SleepingPlace $place): Collection
    {
        return collect()
            ->merge($place->property?->relationLoaded('amenities') ? $place->property->amenities : [])
            ->merge($place->room?->relationLoaded('amenities') ? $place->room->amenities : [])
            ->merge($place->relationLoaded('amenities') ? $place->amenities : []);
    }

    /**
     * @return Collection<int, Rule>
     */
    private function rulesForCard(SleepingPlace $place): Collection
    {
        return collect()
            ->merge($place->property?->relationLoaded('rules') ? $place->property->rules : [])
            ->merge($place->room?->relationLoaded('rules') ? $place->room->rules : [])
            ->merge($place->relationLoaded('rules') ? $place->rules : []);
    }

    private function amenityLabel(Amenity $amenity, string $locale): string
    {
        $translation = $this->translations->resolve($amenity->translations, $locale, config('app.fallback_locale', 'en'));

        return $translation?->name ?: __('listing_card.amenities.fallback');
    }

    private function ruleLabel(Rule $rule, string $locale): string
    {
        $translation = $this->translations->resolve($rule->translations, $locale, config('app.fallback_locale', 'en'));

        return $translation?->name ?: __('listing_card.rules.fallback');
    }
}
