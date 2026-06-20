<?php

namespace App\Livewire\Waitlist;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\PricingService;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class MyWaitlist extends Component
{
    #[Url(as: 'place', except: null)]
    public ?int $sleepingPlaceId = null;

    public string $desiredCheckIn = '';

    public string $desiredCheckOut = '';

    public string $maxPrice = '';

    public bool $notifyAvailable = true;

    public bool $notifyPriceDrop = true;

    public bool $readyToBook = true;

    public bool $autoRequest = false;

    public function save(): void
    {
        $validated = $this->validate([
            'sleepingPlaceId' => ['required', 'integer', 'exists:sleeping_places,id'],
            'desiredCheckIn' => ['required', 'date', 'after_or_equal:today'],
            'desiredCheckOut' => ['required', 'date', 'after:desiredCheckIn'],
            'maxPrice' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'notifyAvailable' => ['boolean'],
            'notifyPriceDrop' => ['boolean'],
            'readyToBook' => ['boolean'],
            'autoRequest' => ['boolean'],
        ], attributes: [
            'sleepingPlaceId' => __('decision.waitlist.fields.sleeping_place'),
            'desiredCheckIn' => __('decision.waitlist.fields.desired_check_in'),
            'desiredCheckOut' => __('decision.waitlist.fields.desired_check_out'),
            'maxPrice' => __('decision.waitlist.fields.max_price'),
        ]);

        $place = SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'base_price_per_night',
                'currency',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'max_guests',
                'min_nights',
                'max_nights',
            ])
            ->findOrFail($validated['sleepingPlaceId']);
        $priceAtJoin = $this->priceAtJoin($place, $validated['desiredCheckIn'], $validated['desiredCheckOut']);

        WaitlistItem::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'sleeping_place_id' => $place->id,
            ],
            [
                'desired_check_in' => $validated['desiredCheckIn'],
                'desired_check_out' => $validated['desiredCheckOut'],
                'max_price' => $validated['maxPrice'] === '' ? null : $validated['maxPrice'],
                'price_at_join' => $priceAtJoin,
                'ready_to_book' => $validated['readyToBook'],
                'auto_request' => $validated['autoRequest'],
                'notify_available' => $validated['notifyAvailable'],
                'notify_price_drop' => $validated['notifyPriceDrop'],
                'notified' => false,
                'notified_at' => null,
                'status' => 'waiting',
            ]
        );

        $this->resetForm();
        session()->flash('decision-waitlist-status', __('decision.waitlist.saved_status'));
        unset($this->items);
    }

    public function remove(int $id): void
    {
        WaitlistItem::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['status' => 'cancelled']);

        unset($this->items);
    }

    #[Computed]
    public function favoriteOptions(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        $locales = $this->translationLocales();

        return $user
            ->favorites()
            ->select(['id', 'user_id', 'sleeping_place_id'])
            ->whereNotNull('sleeping_place_id')
            ->with([
                'sleepingPlace' => fn ($query) => $query
                    ->select(['id', 'place_number', 'display_name'])
                    ->with(['translations' => fn ($translation) => $translation
                        ->select(['id', 'sleeping_place_id', 'locale', 'title'])
                        ->whereIn('locale', $locales)]),
            ])
            ->latest()
            ->limit(20)
            ->get()
            ->filter(fn ($favorite): bool => $favorite->sleepingPlace instanceof SleepingPlace)
            ->map(fn ($favorite): array => [
                'id' => $favorite->sleepingPlace->id,
                'title' => $this->title($favorite->sleepingPlace),
            ])
            ->values();
    }

    #[Computed]
    public function items(): Collection
    {
        $locales = $this->translationLocales();

        return WaitlistItem::query()
            ->select([
                'id',
                'user_id',
                'sleeping_place_id',
                'desired_check_in',
                'desired_check_out',
                'max_price',
                'price_at_join',
                'ready_to_book',
                'auto_request',
                'notify_available',
                'notify_price_drop',
                'notified',
                'notified_at',
                'status',
                'created_at',
            ])
            ->where('user_id', auth()->id())
            ->where('status', 'waiting')
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
                        'currency',
                    ])
                    ->with([
                        'translations' => fn ($translation) => $translation
                            ->select(['id', 'sleeping_place_id', 'locale', 'title'])
                            ->whereIn('locale', $locales),
                        'room:id,property_id,type,status,gender_policy',
                        'property:id,city_id,city,district,status',
                        'property.cityModel:id,name',
                    ]),
            ])
            ->latest()
            ->limit(30)
            ->get()
            ->filter(fn (WaitlistItem $item): bool => $item->sleepingPlace instanceof SleepingPlace)
            ->map(fn (WaitlistItem $item): array => $this->card($item))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.waitlist.my-waitlist', [
            'items' => $this->items,
            'favoriteOptions' => $this->favoriteOptions,
        ])->layout('layouts.app', [
            'title' => __('decision.waitlist.title'),
        ]);
    }

    private function resetForm(): void
    {
        $this->sleepingPlaceId = null;
        $this->desiredCheckIn = '';
        $this->desiredCheckOut = '';
        $this->maxPrice = '';
        $this->notifyAvailable = true;
        $this->notifyPriceDrop = true;
        $this->readyToBook = true;
        $this->autoRequest = false;
    }

    /**
     * @return array<string, mixed>
     */
    private function card(WaitlistItem $item): array
    {
        $place = $item->sleepingPlace;
        $currency = strtoupper($place->currency ?: 'EUR');

        return [
            'item' => $item,
            'title' => $this->title($place),
            'location' => $this->location($place),
            'room_type' => $this->label($place->room?->type),
            'sleeping_place_type' => $this->label($place->type),
            'current_price' => $this->money((float) $place->base_price_per_night, $currency),
            'max_price' => $item->max_price === null ? __('decision.waitlist.no_max_price') : $this->money((float) $item->max_price, $currency),
            'price_at_join' => $item->price_at_join === null ? null : $this->money((float) $item->price_at_join, $currency),
            'url' => route('places.show', [
                'locale' => app()->getLocale(),
                'sleepingPlace' => $place,
                'in' => $item->desired_check_in?->toDateString(),
                'out' => $item->desired_check_out?->toDateString(),
            ]),
        ];
    }

    private function priceAtJoin(SleepingPlace $place, string $checkIn, string $checkOut): float
    {
        try {
            $guest = auth()->user();
            $guest = $guest instanceof User ? $guest : new User;

            return app(PricingService::class)
                ->calculate($guest, $place, $checkIn, $checkOut)
                ->totalAmount;
        } catch (\Throwable) {
            return (float) $place->base_price_per_night;
        }
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
