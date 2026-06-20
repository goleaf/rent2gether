<?php

namespace App\Livewire\Places;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\MediaItem;
use App\Models\SleepingPlace;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SimilarSleepingPlaces extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function placeholder(): View
    {
        return view('livewire.places.partials.lazy-placeholder', [
            'label' => __('listing.detail.similar.loading'),
        ]);
    }

    public function render(): View
    {
        $current = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id'])
            ->with(['property:id,city_id'])
            ->findOrFail($this->sleepingPlaceId);

        $locales = $this->translationLocales();
        $places = SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'status',
                'display_name',
                'place_number',
                'base_price_per_night',
                'currency',
            ])
            ->whereKeyNot($current->id)
            ->where('status', SleepingPlaceStatus::Active->value)
            ->whereHas('room', fn ($room) => $room->where('status', RoomStatus::Active->value))
            ->whereHas('property', function ($property) use ($current): void {
                $property->where('status', PropertyStatus::Active->value)
                    ->where('city_id', $current->property?->city_id);
            })
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'sleeping_place_id', 'locale', 'title'])
                    ->whereIn('locale', $locales),
                'cardMedia' => fn ($media) => $media->select(['id', 'mediable_type', 'mediable_id', 'disk', 'path', 'thumb_path', 'thumbnail_path', 'mobile_path', 'full_path', 'alt_text', 'caption_en', 'caption_ru', 'sort_order', 'is_primary', 'is_cover', 'status']),
                'property:id,city_id,city,district',
                'property.cityModel:id,name',
                'room:id,property_id,type',
            ])
            ->orderBy('base_price_per_night')
            ->limit(3)
            ->get()
            ->map(fn (SleepingPlace $place): array => $this->card($place))
            ->all();

        return view('livewire.places.similar-sleeping-places', [
            'places' => $places,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(SleepingPlace $place): array
    {
        $media = $place->cardMedia;
        $title = $this->title($place);

        return [
            'id' => $place->id,
            'title' => $title,
            'location' => collect([
                $place->property?->cityModel?->name ?: $place->property?->city,
                $place->property?->district,
            ])->filter()->join(', ') ?: __('search.card.location_missing'),
            'price' => (float) $place->base_price_per_night,
            'currency' => $place->currency,
            'image_url' => $media instanceof MediaItem ? $media->imageUrl('mobile') : null,
            'image_alt' => $media instanceof MediaItem ? ($media->localizedCaption() ?: $title) : $title,
            'href' => route('places.show', ['locale' => app()->getLocale(), 'sleepingPlace' => $place]),
        ];
    }

    private function title(SleepingPlace $place): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $place->translations,
            app()->getLocale(),
            'en',
        );

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    /**
     * @return list<string>
     */
    private function translationLocales(): array
    {
        return array_values(array_unique(array_filter([
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
            'en',
            'ru',
        ])));
    }
}
