<?php

namespace App\Livewire\Compare;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CompatibilityService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\PricingService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class ComparePlaces extends Component
{
    #[Url(as: 'places', except: '')]
    public string $places = '';

    #[Url(as: 'in', except: '')]
    public string $checkIn = '';

    #[Url(as: 'out', except: '')]
    public string $checkOut = '';

    #[Url(as: 'guests', except: 1)]
    public int $guestsCount = 1;

    public function removePlace(int $sleepingPlaceId): void
    {
        $this->places = collect($this->ids())
            ->reject(fn (int $id): bool => $id === $sleepingPlaceId)
            ->implode(',');

        unset($this->cards);
    }

    #[Computed]
    public function cards(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $locales = $this->translationLocales();
        $places = SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'status',
                'place_number',
                'display_name',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'max_guests',
                'min_nights',
                'max_nights',
                'has_locker',
            ])
            ->whereIn('id', $ids)
            ->withCount([
                'reviews as published_reviews_count' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ])
            ->withAvg([
                'reviews as published_reviews_rating' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ], 'overall_rating')
            ->with([
                'translations' => fn ($translation) => $translation
                    ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary'])
                    ->whereIn('locale', $locales),
                'room:id,property_id,type,status,gender_policy,beds_count,max_guests,occupied_places_count,has_desk,has_chair',
                'room.amenities:id,slug,category,status',
                'property:id,city_id,host_user_id,type,status,city,district,kitchens_count,amenities',
                'property.cityModel:id,name',
                'property.amenities:id,slug,category,status',
                'property.host:id,name,rating_as_host,identity_verified',
                'property.host.hostProfile:id,user_id,rating_average,reviews_count,default_cancellation_policy',
                'amenities:id,slug,category,status',
                'cardMedia:id,mediable_type,mediable_id,disk,path,thumb_path,thumbnail_path,mobile_path,full_path,alt_text,caption_en,caption_ru,is_primary,is_cover,sort_order,status',
            ])
            ->get()
            ->sortBy(fn (SleepingPlace $place): int => array_search($place->id, $ids, true))
            ->values();

        return $places->map(fn (SleepingPlace $place): array => $this->card($place));
    }

    public function render(): View
    {
        return view('livewire.compare.compare-places', [
            'cards' => $this->cards,
        ])->layout('layouts.app', [
            'title' => __('decision.compare.title'),
        ]);
    }

    /**
     * @return list<int>
     */
    private function ids(): array
    {
        return collect(explode(',', $this->places))
            ->map(fn (string $id): int => (int) trim($id))
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function card(SleepingPlace $place): array
    {
        $currency = strtoupper($place->currency ?: 'EUR');
        $quote = $this->quote($place);
        $compatibility = $this->compatibility($place);
        $hostProfile = $place->property?->host?->hostProfile;
        $rating = $place->published_reviews_rating ?: $hostProfile?->rating_average ?: $place->property?->host?->rating_as_host;

        return [
            'id' => $place->id,
            'title' => $this->title($place),
            'photo' => $place->cardMedia?->imageUrl('mobile'),
            'photo_alt' => $place->cardMedia?->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $this->title($place)]),
            'url' => route('places.show', array_filter([
                'locale' => app()->getLocale(),
                'sleepingPlace' => $place,
                'in' => $this->checkIn,
                'out' => $this->checkOut,
                'guests' => $this->guestsCount,
            ])),
            'price_night' => $this->money((float) $place->base_price_per_night, $currency),
            'total_price' => $quote ? $this->money((float) $quote['total_amount'], $currency) : __('decision.compare.choose_dates'),
            'deposit' => $this->money((float) ($quote['deposit_amount'] ?? $place->deposit_amount ?? 0), $currency),
            'people_in_room' => trans_choice('search.card.people_in_room', (int) ($place->room?->occupied_places_count ?: 0), [
                'count' => (int) ($place->room?->occupied_places_count ?: 0),
            ]),
            'room_type' => $this->label($place->room?->type),
            'bed_type' => $this->label($place->type),
            'locker' => $this->yesNo((bool) $place->has_locker),
            'wifi' => $this->yesNo($this->hasAmenity($place, ['wifi', 'wi-fi', 'fast_wifi'])),
            'kitchen' => $this->yesNo($this->hasAmenity($place, ['kitchen']) || (int) ($place->property?->kitchens_count ?? 0) > 0),
            'cancellation' => $this->valueLabel($hostProfile?->default_cancellation_policy ?: 'flexible'),
            'rating' => $rating ? __('listing.detail.summary.rating_value', ['rating' => number_format((float) $rating, 1)]) : __('listing.detail.summary.no_reviews'),
            'compatibility_score' => $compatibility ? __('decision.compare.score_value', ['score' => $compatibility['score']]) : __('decision.compare.no_score'),
            'warnings' => $compatibility && $compatibility['warning_reasons'] !== []
                ? array_slice($compatibility['warning_reasons'], 0, 2)
                : [__('decision.compare.no_warnings')],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function quote(SleepingPlace $place): ?array
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return null;
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn);
            $checkOut = CarbonImmutable::parse($this->checkOut);

            if ($checkOut->lessThanOrEqualTo($checkIn)) {
                return null;
            }

            $guest = auth()->user();
            $guest = $guest instanceof User ? $guest : new User;

            return app(PricingService::class)
                ->calculate($guest, $place, $checkIn, $checkOut, max(1, $this->guestsCount))
                ->toArray();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function compatibility(SleepingPlace $place): ?array
    {
        $guest = auth()->user();

        if (! $guest instanceof User) {
            return null;
        }

        return app(CompatibilityService::class)->check($guest, $place);
    }

    /**
     * @param  list<string>  $slugs
     */
    private function hasAmenity(SleepingPlace $place, array $slugs): bool
    {
        $propertyAmenities = $place->property?->relationLoaded('amenities')
            ? $place->property->getRelation('amenities')->pluck('slug')
            : collect($place->property?->getAttribute('amenities') ?? []);

        $values = collect()
            ->merge($place->amenities->pluck('slug'))
            ->merge($place->room?->amenities?->pluck('slug') ?? [])
            ->merge($propertyAmenities)
            ->filter()
            ->map(fn (string $slug): string => Str::of($slug)->lower()->replace('-', '_')->toString());

        return $values->intersect($slugs)->isNotEmpty();
    }

    private function title(SleepingPlace $place): string
    {
        $translation = $this->translation($place->translations);

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    private function translation(Collection $translations): ?object
    {
        return app(LocalizedModelContentResolver::class)->resolve(
            $translations,
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
        );
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

    private function label(mixed $value): string
    {
        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        return $this->valueLabel($value);
    }

    private function valueLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('listing.detail.values.not_set');
        }

        $key = 'listing.detail.values.'.Str::slug((string) $value, '_');

        return Lang::has($key) ? __($key) : __('listing.detail.values.unknown');
    }

    private function yesNo(bool $value): string
    {
        return $value ? __('decision.common.yes') : __('decision.common.no');
    }

    private function money(float|int|string $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
