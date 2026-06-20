<?php

namespace App\Services\Listings;

use App\Models\MediaItem;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ListingCardPrivacyService
{
    public function __construct(private readonly LocalizedModelContentResolver $translations) {}

    public function title(SleepingPlace $place, string $locale): string
    {
        $translation = $this->resolve($place->translations, $locale);

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    public function summary(SleepingPlace $place, string $locale): ?string
    {
        $translation = $this->resolve($place->translations, $locale);

        return $translation?->summary;
    }

    public function location(?Property $property): string
    {
        $parts = array_filter([
            $property?->cityModel?->name ?: (is_string($property?->getAttribute('city')) ? $property->getAttribute('city') : null),
            $property?->district,
        ]);

        return $parts === [] ? __('listing_card.location_missing') : implode(' · ', $parts);
    }

    public function cityName(?Property $property): ?string
    {
        return $property?->cityModel?->name
            ?: (is_string($property?->getAttribute('city')) ? $property->getAttribute('city') : null);
    }

    public function primaryMedia(SleepingPlace $place): ?MediaItem
    {
        return $place->cardMedia ?: $place->room?->cardMedia ?: $place->property?->cardMedia;
    }

    public function imageAlt(?MediaItem $media, string $title): string
    {
        return $media?->localizedCaption() ?: __('listing_card.photo_alt', ['title' => $title]);
    }

    /**
     * @param  EloquentCollection<int, object>  $translations
     */
    private function resolve(EloquentCollection $translations, string $locale): ?object
    {
        return $this->translations->resolve(
            $translations,
            $locale,
            config('app.fallback_locale', 'en'),
        );
    }
}
