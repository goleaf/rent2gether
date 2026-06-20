<?php

namespace App\Livewire\Compare;

use App\Data\Favorites\FavoriteContext;
use App\Data\Listings\ListingCardContext;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\CompatibilityService;
use App\Services\Favorites\FavoriteService;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\Localization\SupportedContentLocales;
use App\Services\PricingService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
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

    public function saveComparedToFavorites(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        foreach ($this->ids() as $sleepingPlaceId) {
            $favorites->add(
                user: $user,
                sleepingPlaceId: $sleepingPlaceId,
                collectionId: null,
                context: new FavoriteContext(
                    source: 'comparison',
                    checkIn: $this->checkIn ?: null,
                    checkOut: $this->checkOut ?: null,
                    guestsCount: max(1, $this->guestsCount),
                ),
            );
        }

        $this->dispatch('favorite-collections-changed');
    }

    #[Computed]
    public function cards(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $context = $this->listingCardContext($ids);
        $places = app(ListingCardQueryService::class)
            ->forComparison($ids, $context)
            ->get()
            ->sortBy(fn (SleepingPlace $place): int => array_search($place->id, $ids, true))
            ->values();
        $listingCards = app(ListingCardService::class)
            ->buildMany($places, $context)
            ->keyBy(fn ($card) => $card->sleepingPlaceId);

        return $places->map(fn (SleepingPlace $place): array => $this->card(
            $place,
            $listingCards->get($place->id)?->toArray(),
        ));
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
    private function card(SleepingPlace $place, ?array $listingCard = null): array
    {
        $currency = strtoupper($place->currency ?: 'EUR');
        $quote = $this->quote($place);
        $compatibility = $this->compatibility($place);
        $hostProfile = $place->property?->host?->hostProfile;
        $rating = $place->published_reviews_rating ?: $hostProfile?->rating_average ?: $place->property?->host?->rating_as_host;

        return [
            'id' => $place->id,
            'listing_card' => $listingCard,
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
            'available_for_dates' => $this->availableForDates($place),
        ];
    }

    /**
     * @param  list<int>  $ids
     */
    private function listingCardContext(array $ids): ListingCardContext
    {
        return new ListingCardContext(
            userId: auth()->id() ? (int) auth()->id() : null,
            locale: app()->getLocale(),
            currency: 'EUR',
            checkInDate: $this->checkIn ?: null,
            checkOutDate: $this->checkOut ?: null,
            guestsCount: max(1, $this->guestsCount),
            source: 'comparison',
            filters: [
                'variant' => 'comparison',
                'comparison_ids' => $ids,
            ],
        );
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

    private function availableForDates(SleepingPlace $place): ?bool
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return null;
        }

        try {
            return app(AvailabilityService::class)
                ->isAvailable($place, $this->checkIn, $this->checkOut);
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
        return app(SupportedContentLocales::class)->preferred();
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
