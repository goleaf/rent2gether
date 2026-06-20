<?php

namespace App\Livewire\SavedSearches;

use App\Data\Listings\ListingCardContext;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use App\Services\Localization\LocalizedModelContentResolver;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SavedSearchResultsList extends Component
{
    #[Locked]
    public int $savedSearchId;

    public string $section = 'all';

    public int $visibleCount = 12;

    public function mount(int $savedSearchId, string $section = 'all'): void
    {
        $this->savedSearchId = $savedSearchId;
        $this->section = $section;
    }

    public function loadMore(): void
    {
        $this->visibleCount = min(80, $this->visibleCount + 12);
        unset($this->cards);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function cards(): array
    {
        $query = SavedSearchResult::query()
            ->select([
                'id',
                'saved_search_id',
                'sleeping_place_id',
                'property_id',
                'room_id',
                'status',
                'match_score',
                'price_per_night_snapshot',
                'total_price_snapshot',
                'current_price_per_night',
                'current_total_price',
                'current_deposit',
                'price_changed',
                'price_change_amount',
                'became_unavailable',
                'became_available_again',
                'is_new_match',
                'last_matched_at',
                'created_at',
            ])
            ->where('saved_search_id', $this->savedSearchId)
            ->with([
                'savedSearch:id,check_in_date,check_out_date,guests_count,budget_max,total_budget_max,currency',
                'sleepingPlace' => fn ($place) => $place
                    ->select([
                        'id',
                        'room_id',
                        'property_id',
                        'type',
                        'status',
                        'place_number',
                        'display_name',
                        'base_price_per_night',
                        'deposit_amount',
                        'currency',
                        'instant_booking_enabled',
                    ])
                    ->withCount([
                        'reviews as published_reviews_count' => fn (Builder $review) => $review->visible()->guestToPlace(),
                    ])
                    ->withAvg([
                        'reviews as published_reviews_rating' => fn (Builder $review) => $review->visible()->guestToPlace(),
                    ], 'overall_rating')
                    ->with([
                        'translations:id,sleeping_place_id,locale,title,summary',
                        'room:id,property_id,type,status,gender_policy,beds_count,max_guests',
                        'property:id,city_id,host_user_id,type,status,city,district',
                        'property.cityModel:id,name',
                        'cardMedia:id,mediable_type,mediable_id,disk,path,thumb_path,thumbnail_path,mobile_path,full_path,alt_text,caption_en,caption_ru,is_primary,is_cover,sort_order,status',
                    ]),
            ]);

        $this->applySection($query);

        $results = $query
            ->recentlyMatched()
            ->limit($this->visibleCount)
            ->get();
        $listingCards = $this->listingCardsFor($results);

        return $results
            ->map(fn (SavedSearchResult $result): array => $this->card(
                $result,
                $listingCards[(int) $result->sleeping_place_id] ?? null,
            ))
            ->all();
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-search-results-list');
    }

    private function applySection(Builder $query): void
    {
        match ($this->section) {
            'new' => $query->newMatches(),
            'price_drops' => $query->priceDropped(),
            'available_again' => $query->availableAgain(),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function card(SavedSearchResult $result, ?array $listingCard = null): array
    {
        $place = $result->sleepingPlace;
        $currency = strtoupper($place?->currency ?: 'EUR');

        return [
            'id' => $result->id,
            'listing_card' => $listingCard,
            'place_id' => $result->sleeping_place_id,
            'title' => $place instanceof SleepingPlace ? $this->title($place) : __('saved_searches.result_missing'),
            'location' => $place instanceof SleepingPlace ? $this->location($place) : __('search.card.location_missing'),
            'image' => $place?->cardMedia?->imageUrl('mobile'),
            'image_alt' => $place?->cardMedia?->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $place instanceof SleepingPlace ? $this->title($place) : __('saved_searches.result_missing')]),
            'price' => Number::currency((float) ($result->current_price_per_night ?? $result->price_per_night_snapshot ?? 0), $currency, app()->getLocale()),
            'total' => $result->current_total_price === null ? null : Number::currency((float) $result->current_total_price, $currency, app()->getLocale()),
            'deposit' => Number::currency((float) ($result->current_deposit ?? 0), $currency, app()->getLocale()),
            'price_state' => $result->price_changed ? ((float) $result->price_change_amount < 0 ? 'dropped' : 'increased') : 'same',
            'price_change' => $result->price_change_amount === null ? null : Number::currency(abs((float) $result->price_change_amount), $currency, app()->getLocale()),
            'availability_state' => $result->became_available_again ? 'available_again' : ($result->became_unavailable || $result->status === 'unavailable' ? 'unavailable' : 'available'),
            'check_in' => $result->savedSearch?->check_in_date?->toDateString(),
            'check_out' => $result->savedSearch?->check_out_date?->toDateString(),
            'guests_count' => (int) ($result->savedSearch?->guests_count ?: 1),
            'max_price' => $result->savedSearch?->budget_max,
            'max_total_price' => $result->savedSearch?->total_budget_max,
            'match_score' => $result->match_score,
            'rating' => $place?->published_reviews_rating ? __('listing.detail.summary.rating_value', ['rating' => number_format((float) $place->published_reviews_rating, 1)]) : __('listing.detail.summary.no_reviews'),
            'room_type' => $this->label($place?->room?->type),
            'sleeping_place_type' => $this->label($place?->type),
            'url' => $place instanceof SleepingPlace ? route('places.show', ['locale' => app()->getLocale(), 'sleepingPlace' => $place]) : route('saved-searches.index', ['locale' => app()->getLocale()]),
            'book_url' => $place instanceof SleepingPlace ? route('places.book', ['locale' => app()->getLocale(), 'sleepingPlace' => $place]) : null,
        ];
    }

    /**
     * @param  Collection<int, SavedSearchResult>  $results
     * @return array<int, array<string, mixed>>
     */
    private function listingCardsFor(Collection $results): array
    {
        if ($results->isEmpty()) {
            return [];
        }

        $placeIds = $results
            ->pluck('sleeping_place_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($placeIds === []) {
            return [];
        }

        $firstSearch = $results->first()?->savedSearch;
        $availabilityById = $results
            ->mapWithKeys(fn (SavedSearchResult $result): array => [
                (int) $result->sleeping_place_id => ! ($result->became_unavailable || $result->status === 'unavailable'),
            ])
            ->all();
        $context = new ListingCardContext(
            userId: auth()->id() ? (int) auth()->id() : null,
            locale: app()->getLocale(),
            currency: strtoupper($firstSearch?->currency ?: 'EUR'),
            checkInDate: $firstSearch?->check_in_date?->toDateString(),
            checkOutDate: $firstSearch?->check_out_date?->toDateString(),
            guestsCount: max(1, (int) ($firstSearch?->guests_count ?: 1)),
            source: 'saved_search',
            filters: [
                'variant' => 'compact',
                'availability_by_id' => $availabilityById,
            ],
        );
        $places = app(ListingCardQueryService::class)
            ->forComparison($placeIds, $context)
            ->get()
            ->sortBy(fn (SleepingPlace $place): int => array_search($place->id, $placeIds, true))
            ->values();
        $cards = [];

        foreach (app(ListingCardService::class)->buildMany($places, $context) as $card) {
            $cards[$card->sleepingPlaceId] = $card->toArray();
        }

        return $cards;
    }

    private function title(SleepingPlace $place): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
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
        $parts = array_filter([
            $place->property?->cityModel?->name ?: $place->property?->city,
            $place->property?->district,
        ]);

        return $parts === [] ? __('search.card.location_missing') : implode(', ', $parts);
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
}
