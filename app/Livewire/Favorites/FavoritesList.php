<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
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
use Livewire\Component;

class FavoritesList extends Component
{
    public string $selectedCollection = '';

    /** @var list<int> */
    public array $selectedForCompare = [];

    public function remove(int $favoriteId): void
    {
        Favorite::query()
            ->where('id', $favoriteId)
            ->where('user_id', auth()->id())
            ->delete();

        unset($this->favoriteCards, $this->collections);
    }

    public function updateNote(int $favoriteId, string $note): void
    {
        Favorite::query()
            ->where('id', $favoriteId)
            ->where('user_id', auth()->id())
            ->update(['note' => Str::limit(trim($note), 1000, '')]);

        unset($this->favoriteCards);
    }

    public function updatePriority(int $favoriteId, mixed $priority): void
    {
        Favorite::query()
            ->where('id', $favoriteId)
            ->where('user_id', auth()->id())
            ->update(['priority' => max(0, min(9, (int) $priority))]);

        unset($this->favoriteCards);
    }

    public function toggleCompare(int $sleepingPlaceId): void
    {
        if (in_array($sleepingPlaceId, $this->selectedForCompare, true)) {
            $this->selectedForCompare = array_values(array_diff($this->selectedForCompare, [$sleepingPlaceId]));

            return;
        }

        if (count($this->selectedForCompare) >= 4) {
            $this->addError('selectedForCompare', __('decision.compare.limit_warning'));

            return;
        }

        $this->selectedForCompare[] = $sleepingPlaceId;
    }

    public function compareSelected(): void
    {
        if ($this->selectedForCompare === []) {
            $this->addError('selectedForCompare', __('decision.compare.choose_first'));

            return;
        }

        $this->redirect(route('compare.index', [
            'locale' => app()->getLocale(),
            'places' => implode(',', array_slice($this->selectedForCompare, 0, 4)),
        ]), navigate: true);
    }

    #[Computed]
    public function collections(): Collection
    {
        return Favorite::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('collection')
            ->where('collection', '!=', '')
            ->distinct()
            ->orderBy('collection')
            ->pluck('collection');
    }

    #[Computed]
    public function favoriteCards(): Collection
    {
        $locales = $this->translationLocales();

        return Favorite::query()
            ->select([
                'id',
                'user_id',
                'sleeping_place_id',
                'collection',
                'note',
                'priority',
                'price_at_save',
                'check_in',
                'check_out',
                'guests_count',
                'notify_available',
                'notify_price_drop',
                'created_at',
            ])
            ->where('user_id', auth()->id())
            ->whereNotNull('sleeping_place_id')
            ->when($this->selectedCollection !== '', fn (Builder $query) => $query->where('collection', $this->selectedCollection))
            ->with([
                'sleepingPlace' => fn ($query) => $query
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
                        'min_nights',
                        'max_nights',
                        'max_guests',
                        'has_locker',
                    ])
                    ->with([
                        'translations' => fn ($translation) => $translation
                            ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary'])
                            ->whereIn('locale', $locales),
                        'room:id,property_id,type,status,gender_policy,beds_count,max_guests,occupied_places_count',
                        'property:id,city_id,host_user_id,type,status,city,district',
                        'property.cityModel:id,name',
                        'cardMedia:id,mediable_type,mediable_id,disk,path,thumb_path,thumbnail_path,mobile_path,full_path,alt_text,caption_en,caption_ru,is_primary,is_cover,sort_order,status',
                    ]),
            ])
            ->orderByDesc('priority')
            ->latest()
            ->limit(40)
            ->get()
            ->filter(fn (Favorite $favorite): bool => $favorite->sleepingPlace instanceof SleepingPlace)
            ->map(fn (Favorite $favorite): array => $this->card($favorite))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.favorites.favorites-list', [
            'cards' => $this->favoriteCards,
            'collections' => $this->collections,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Favorite $favorite): array
    {
        $place = $favorite->sleepingPlace;
        $currentPrice = $this->currentPrice($favorite, $place);
        $savedPrice = $favorite->price_at_save === null ? null : (float) $favorite->price_at_save;
        $priceState = $this->priceState($savedPrice, $currentPrice);
        $availabilityChanged = $this->availabilityChanged($favorite, $place);
        $currency = strtoupper($place->currency ?: 'EUR');

        return [
            'favorite' => $favorite,
            'place_id' => $place->id,
            'title' => $this->title($place),
            'location' => $this->location($place),
            'room_type' => $this->label($place->room?->type),
            'sleeping_place_type' => $this->label($place->type),
            'saved_price' => $savedPrice === null ? null : $this->money($savedPrice, $currency),
            'current_price' => $this->money($currentPrice, $currency),
            'current_price_label' => $favorite->check_in && $favorite->check_out
                ? __('decision.favorites.current_total')
                : __('decision.favorites.current_nightly'),
            'price_state' => $priceState,
            'availability_changed' => $availabilityChanged,
            'dates' => $this->dateSummary($favorite),
            'image' => $place->cardMedia?->imageUrl('mobile'),
            'image_alt' => $place->cardMedia?->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $this->title($place)]),
            'url' => route('places.show', array_filter([
                'locale' => app()->getLocale(),
                'sleepingPlace' => $place,
                'in' => $favorite->check_in?->toDateString(),
                'out' => $favorite->check_out?->toDateString(),
                'guests' => $favorite->guests_count ?: 1,
            ])),
        ];
    }

    private function currentPrice(Favorite $favorite, SleepingPlace $place): float
    {
        if (! $favorite->check_in || ! $favorite->check_out) {
            return (float) $place->base_price_per_night;
        }

        try {
            $guest = auth()->user();
            $guest = $guest instanceof User ? $guest : new User;

            return app(PricingService::class)
                ->calculate(
                    $guest,
                    $place,
                    $favorite->check_in,
                    $favorite->check_out,
                    max(1, (int) $favorite->guests_count),
                )
                ->totalAmount;
        } catch (\Throwable) {
            return (float) $place->base_price_per_night;
        }
    }

    private function availabilityChanged(Favorite $favorite, SleepingPlace $place): ?bool
    {
        if (! $favorite->check_in || ! $favorite->check_out) {
            return null;
        }

        try {
            return ! app(AvailabilityService::class)
                ->isAvailable($place, $favorite->check_in, $favorite->check_out);
        } catch (\Throwable) {
            return true;
        }
    }

    private function priceState(?float $savedPrice, float $currentPrice): string
    {
        if ($savedPrice === null) {
            return 'unknown';
        }

        if ($currentPrice < $savedPrice - 0.01) {
            return 'dropped';
        }

        if ($currentPrice > $savedPrice + 0.01) {
            return 'increased';
        }

        return 'same';
    }

    private function dateSummary(Favorite $favorite): string
    {
        if (! $favorite->check_in || ! $favorite->check_out) {
            return __('decision.favorites.no_dates');
        }

        $nights = (int) CarbonImmutable::instance($favorite->check_in)->diffInDays($favorite->check_out);

        return __('decision.favorites.dates_summary', [
            'check_in' => $favorite->check_in->toFormattedDateString(),
            'check_out' => $favorite->check_out->toFormattedDateString(),
            'nights' => trans_choice('booking.nights_count', $nights, ['count' => $nights]),
        ]);
    }

    private function title(SleepingPlace $place): string
    {
        $translation = $this->translation($place->translations);

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

        if ($value === null || $value === '') {
            return __('listing.detail.values.not_set');
        }

        $key = 'listing.detail.values.'.Str::slug((string) $value, '_');

        return Lang::has($key) ? __($key) : __('listing.detail.values.unknown');
    }

    private function money(float|int|string $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
