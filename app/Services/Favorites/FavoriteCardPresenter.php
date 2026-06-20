<?php

namespace App\Services\Favorites;

use App\Data\Listings\ListingCardContext;
use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Models\WaitlistItem;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use App\Services\Localization\LocalizedModelContentResolver;
use BackedEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class FavoriteCardPresenter
{
    public function __construct(
        private readonly LocalizedModelContentResolver $translations,
        private readonly ListingCardQueryService $listingQueries,
        private readonly ListingCardService $listingCards,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Favorite $favorite, ?array $listingCard = null): array
    {
        $place = $favorite->sleepingPlace;
        $currency = strtoupper($favorite->currency ?: $place?->currency ?: 'EUR');
        $currentNight = $favorite->current_price_per_night ?? $favorite->price_per_night_snapshot ?? $place?->base_price_per_night ?? 0;
        $currentTotal = $favorite->current_total_price ?? $favorite->total_price_snapshot;
        $deposit = $favorite->current_deposit ?? $favorite->deposit_snapshot ?? $place?->deposit_amount ?? 0;

        return [
            'id' => $favorite->id,
            'favorite_id' => $favorite->id,
            'collection_id' => $favorite->favorite_collection_id,
            'listing_card' => $listingCard,
            'place_id' => $favorite->sleeping_place_id,
            'title' => $place instanceof SleepingPlace ? $this->title($place) : __('favorites.place_missing'),
            'location' => $place instanceof SleepingPlace ? $this->location($place) : __('search.card.location_missing'),
            'image' => $place?->cardMedia?->imageUrl('mobile'),
            'image_alt' => $place?->cardMedia?->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $place instanceof SleepingPlace ? $this->title($place) : __('favorites.place_missing')]),
            'url' => $place instanceof SleepingPlace ? $this->placeUrl($favorite, $place) : route('favorites.index', ['locale' => app()->getLocale()]),
            'book_url' => $place instanceof SleepingPlace ? route('places.book', [
                'locale' => app()->getLocale(),
                'sleepingPlace' => $place,
            ]) : null,
            'price_per_night' => $this->money((float) $currentNight, $currency),
            'total_price' => $currentTotal === null ? null : $this->money((float) $currentTotal, $currency),
            'deposit' => $this->money((float) $deposit, $currency),
            'saved_price' => $favorite->total_price_snapshot === null ? null : $this->money((float) $favorite->total_price_snapshot, $currency),
            'rating' => $this->rating($place),
            'reviews_count' => (int) ($place?->published_reviews_count ?? 0),
            'availability_state' => $this->availabilityState($favorite),
            'check_in' => ($favorite->check_in_date ?: $favorite->check_in)?->toDateString(),
            'check_out' => ($favorite->check_out_date ?: $favorite->check_out)?->toDateString(),
            'guests_count' => (int) ($favorite->guests_count ?: 1),
            'price_state' => $this->priceState($favorite),
            'price_change' => $favorite->price_change_amount === null ? null : $this->money(abs((float) $favorite->price_change_amount), $currency),
            'note' => $favorite->noteText(),
            'priority' => (int) $favorite->priority,
            'priority_label' => $this->priorityLabel((int) $favorite->priority),
            'decision_status' => $favorite->decision_status ?: 'saved',
            'decision_status_label' => __('favorites.decision_statuses.'.($favorite->decision_status ?: 'saved')),
            'dates' => $this->dateSummary($favorite),
            'room_type' => $this->label($place?->room?->type),
            'sleeping_place_type' => $this->label($place?->type),
            'remind_at' => $favorite->remind_at?->toDateString(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function presentMany(Collection $favorites): array
    {
        $listingCards = $this->listingCardsFor($favorites);

        return $favorites
            ->map(fn (Favorite $favorite): array => $this->present(
                $favorite,
                $listingCards[(int) $favorite->sleeping_place_id] ?? null,
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listingCardsFor(Collection $favorites): array
    {
        $favorites = $favorites
            ->filter(fn (Favorite $favorite): bool => $favorite->sleeping_place_id !== null)
            ->values();

        if ($favorites->isEmpty()) {
            return [];
        }

        $userId = (int) ($favorites->first()?->user_id ?: auth()->id());
        $allPlaceIds = $favorites
            ->pluck('sleeping_place_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $waitlistIds = $userId > 0
            ? WaitlistItem::query()
                ->where('user_id', $userId)
                ->whereIn('sleeping_place_id', $allPlaceIds)
                ->whereIn('status', ['active', 'offered', 'awaiting_guest'])
                ->pluck('sleeping_place_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all()
            : [];
        $cards = [];

        foreach ($favorites->groupBy(fn (Favorite $favorite): string => $this->contextKey($favorite)) as $group) {
            $placeIds = $group
                ->pluck('sleeping_place_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($placeIds === []) {
                continue;
            }

            /** @var Favorite $first */
            $first = $group->first();
            $context = $this->listingCardContext($first, $placeIds, $waitlistIds, $group);
            $places = $this->listingQueries
                ->forComparison($placeIds, $context)
                ->get()
                ->sortBy(fn (SleepingPlace $place): int => array_search($place->id, $placeIds, true))
                ->values();

            foreach ($this->listingCards->buildMany($places, $context) as $card) {
                $cards[$card->sleepingPlaceId] = $card->toArray();
            }
        }

        return $cards;
    }

    private function contextKey(Favorite $favorite): string
    {
        $checkIn = ($favorite->check_in_date ?: $favorite->check_in)?->toDateString() ?: 'none';
        $checkOut = ($favorite->check_out_date ?: $favorite->check_out)?->toDateString() ?: 'none';

        return implode('|', [
            $checkIn,
            $checkOut,
            (int) ($favorite->guests_count ?: 1),
            strtoupper($favorite->currency ?: 'EUR'),
        ]);
    }

    /**
     * @param  list<int>  $placeIds
     */
    private function listingCardContext(Favorite $favorite, array $placeIds, array $waitlistIds, Collection $group): ListingCardContext
    {
        $checkIn = $favorite->check_in_date ?: $favorite->check_in;
        $checkOut = $favorite->check_out_date ?: $favorite->check_out;
        $availabilityById = $group
            ->filter(fn (Favorite $item): bool => $item->is_currently_available !== null)
            ->mapWithKeys(fn (Favorite $item): array => [(int) $item->sleeping_place_id => (bool) $item->is_currently_available])
            ->all();

        return new ListingCardContext(
            userId: (int) ($favorite->user_id ?: auth()->id()) ?: null,
            locale: app()->getLocale(),
            currency: strtoupper($favorite->currency ?: 'EUR'),
            checkInDate: $checkIn?->toDateString(),
            checkOutDate: $checkOut?->toDateString(),
            nightsCount: $favorite->nights_count ? (int) $favorite->nights_count : null,
            guestsCount: max(1, (int) ($favorite->guests_count ?: 1)),
            source: 'favorite',
            filters: [
                'variant' => 'favorite',
                'favorite_ids' => $placeIds,
                'waitlist_ids' => $waitlistIds,
                'availability_by_id' => $availabilityById,
            ],
        );
    }

    private function title(SleepingPlace $place): string
    {
        $translation = $this->translations->resolve(
            $place->translations,
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
        );

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    private function location(SleepingPlace $place): string
    {
        $property = $place->property;
        $parts = array_filter([
            $property?->cityModel?->name ?: $property?->city,
            $property?->district,
        ]);

        return $parts === [] ? __('search.card.location_missing') : implode(', ', $parts);
    }

    private function placeUrl(Favorite $favorite, SleepingPlace $place): string
    {
        return route('places.show', array_filter([
            'locale' => app()->getLocale(),
            'sleepingPlace' => $place,
            'in' => $favorite->check_in_date?->toDateString() ?: $favorite->check_in?->toDateString(),
            'out' => $favorite->check_out_date?->toDateString() ?: $favorite->check_out?->toDateString(),
            'guests' => $favorite->guests_count ?: 1,
        ]));
    }

    private function priceState(Favorite $favorite): string
    {
        if (! $favorite->price_changed) {
            return 'same';
        }

        return (float) $favorite->price_change_amount < 0 ? 'dropped' : 'increased';
    }

    private function availabilityState(Favorite $favorite): string
    {
        if ($favorite->became_available_again) {
            return 'available_again';
        }

        if ($favorite->became_unavailable) {
            return 'unavailable';
        }

        if ($favorite->partial_availability) {
            return 'partial';
        }

        if ($favorite->is_currently_available === true) {
            return 'available';
        }

        if ($favorite->is_currently_available === false) {
            return 'unavailable';
        }

        return 'needs_check';
    }

    private function dateSummary(Favorite $favorite): string
    {
        $checkIn = $favorite->check_in_date ?: $favorite->check_in;
        $checkOut = $favorite->check_out_date ?: $favorite->check_out;

        if (! $checkIn || ! $checkOut) {
            return __('favorites.no_dates');
        }

        $nights = (int) ($favorite->nights_count ?: $checkIn->diffInDays($checkOut));

        return __('favorites.dates_summary', [
            'check_in' => $checkIn->toFormattedDateString(),
            'check_out' => $checkOut->toFormattedDateString(),
            'nights' => trans_choice('booking.nights_count', $nights, ['count' => $nights]),
        ]);
    }

    private function rating(?SleepingPlace $place): string
    {
        $rating = $place?->published_reviews_rating;

        return $rating ? __('listing.detail.summary.rating_value', ['rating' => number_format((float) $rating, 1)]) : __('listing.detail.summary.no_reviews');
    }

    private function priorityLabel(int $priority): string
    {
        if ($priority >= 8) {
            return __('favorites.priorities.high');
        }

        if ($priority <= 2) {
            return __('favorites.priorities.low');
        }

        return __('favorites.priorities.normal');
    }

    private function label(mixed $value): string
    {
        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        if ($value === null || $value === '') {
            return __('listing.detail.values.not_set');
        }

        $key = 'listing.detail.values.'.Str::slug((string) $value, '_');

        return Lang::has($key) ? __($key) : __('listing.detail.values.unknown');
    }

    private function money(float $amount, string $currency): string
    {
        return Number::currency($amount, $currency, app()->getLocale());
    }
}
