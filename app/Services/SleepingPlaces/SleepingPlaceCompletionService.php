<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Services\Localization\SupportedContentLocales;

class SleepingPlaceCompletionService
{
    /**
     * @return list<array{key:string,label:string,complete:bool}>
     */
    public function items(SleepingPlace $place): array
    {
        $place->loadMissing([
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]);

        $translation = $place->translations->firstWhere('locale', app()->getLocale())
            ?: $place->translations->firstWhere('locale', config('app.fallback_locale', 'en'))
            ?: $place->translations->first();

        return [
            ['key' => 'title', 'label' => __('sleeping_place.completion.items.title'), 'complete' => filled($translation?->title ?? $place->display_name)],
            ['key' => 'type', 'label' => __('sleeping_place.completion.items.type'), 'complete' => filled($place->sleeping_place_type ?? $place->type)],
            ['key' => 'number', 'label' => __('sleeping_place.completion.items.number'), 'complete' => filled($place->place_number)],
            ['key' => 'dimensions', 'label' => __('sleeping_place.completion.items.dimensions'), 'complete' => $place->physicalDetails !== null && filled($place->physicalDetails->length_cm) && filled($place->physicalDetails->width_cm)],
            ['key' => 'price', 'label' => __('sleeping_place.completion.items.price'), 'complete' => filled($place->base_price_per_night)],
            ['key' => 'calendar', 'label' => __('sleeping_place.completion.items.calendar'), 'complete' => $place->availabilityDays()->exists()],
            ['key' => 'photos', 'label' => __('sleeping_place.completion.items.photos'), 'complete' => $place->mediaItems()->active()->exists()],
            ['key' => 'mattress', 'label' => __('sleeping_place.completion.items.mattress'), 'complete' => filled($place->comfortDetails?->mattress_type) || filled($place->comfortDetails?->mattress_firmness)],
            ['key' => 'bedding', 'label' => __('sleeping_place.completion.items.bedding'), 'complete' => $place->comfortDetails?->has_bedding !== null],
            ['key' => 'towel', 'label' => __('sleeping_place.completion.items.towel'), 'complete' => $place->comfortDetails?->has_towel !== null],
            ['key' => 'locker', 'label' => __('sleeping_place.completion.items.locker'), 'complete' => $place->storageDetails?->has_personal_locker !== null],
            ['key' => 'power_socket', 'label' => __('sleeping_place.completion.items.power_socket'), 'complete' => $place->positionDetails?->has_power_socket !== null],
            ['key' => 'privacy', 'label' => __('sleeping_place.completion.items.privacy'), 'complete' => filled($place->positionDetails?->privacy_level)],
            ['key' => 'condition', 'label' => __('sleeping_place.completion.items.condition'), 'complete' => filled($place->conditionDetails?->condition_state)],
            ['key' => 'translations', 'label' => __('sleeping_place.completion.items.translations'), 'complete' => app(SupportedContentLocales::class)->hasAllTranslations($place->translations, ['title'])],
        ];
    }

    public function percentage(SleepingPlace $place): int
    {
        $items = $this->items($place);
        $complete = count(array_filter($items, fn (array $item): bool => $item['complete']));

        return (int) round(($complete / max(1, count($items))) * 100);
    }
}
