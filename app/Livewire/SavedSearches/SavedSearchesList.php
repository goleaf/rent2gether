<?php

namespace App\Livewire\SavedSearches;

use App\Models\City;
use App\Models\SavedSearch;
use App\Support\Geo\GeoNameNormalizer;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SavedSearchesList extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public ?int $cityId = null;

    public string $cityQuery = '';

    public string $district = '';

    public string $checkIn = '';

    public string $checkOut = '';

    public bool $flexibleDates = false;

    public string $priceMin = '';

    public string $priceMax = '';

    public string $currency = 'EUR';

    public bool $notifyNewMatches = true;

    public bool $notifyPriceDrop = true;

    public bool $notifyAvailability = true;

    /** @var array<string, bool> */
    public array $filters = [
        'wifi' => false,
        'kitchen' => false,
        'locker' => false,
        'quiet_hours' => false,
    ];

    public function updatedCityQuery(): void
    {
        if ($this->cityId !== null) {
            $this->cityId = null;
        }

        unset($this->cityOptions);
    }

    public function selectCity(int $cityId): void
    {
        $city = City::query()
            ->select(['id', 'name'])
            ->findOrFail($cityId);

        $this->cityId = $city->id;
        $this->cityQuery = $city->name;

        unset($this->cityOptions);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'cityId' => ['nullable', 'integer', 'exists:cities,id'],
            'cityQuery' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'checkIn' => ['nullable', 'date', 'after_or_equal:today'],
            'checkOut' => ['nullable', 'date', 'after:checkIn'],
            'flexibleDates' => ['boolean'],
            'priceMin' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'priceMax' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'currency' => ['required', 'string', 'size:3'],
            'notifyNewMatches' => ['boolean'],
            'notifyPriceDrop' => ['boolean'],
            'notifyAvailability' => ['boolean'],
            'filters' => ['array'],
            'filters.*' => ['boolean'],
        ], attributes: [
            'name' => __('decision.saved.fields.name'),
            'cityQuery' => __('decision.saved.fields.city'),
            'district' => __('decision.saved.fields.district'),
            'checkIn' => __('decision.saved.fields.check_in'),
            'checkOut' => __('decision.saved.fields.check_out'),
            'priceMin' => __('decision.saved.fields.price_min'),
            'priceMax' => __('decision.saved.fields.price_max'),
            'currency' => __('decision.saved.fields.currency'),
        ]);

        $priceMin = $validated['priceMin'] === '' ? null : $validated['priceMin'];
        $priceMax = $validated['priceMax'] === '' ? null : $validated['priceMax'];
        $nights = $this->nights($validated['checkIn'] ?: null, $validated['checkOut'] ?: null);

        SavedSearch::query()->updateOrCreate(
            [
                'id' => $this->editingId,
                'user_id' => auth()->id(),
            ],
            [
                'city_id' => $validated['cityId'],
                'locale' => app()->getLocale(),
                'name' => $validated['name'],
                'city' => $validated['cityQuery'],
                'district' => $validated['district'] ?: null,
                'check_in' => $validated['checkIn'] ?: null,
                'check_out' => $validated['checkOut'] ?: null,
                'flexible_dates' => $validated['flexibleDates'],
                'nights' => $nights,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'currency' => strtoupper($validated['currency']),
                'filters_json' => $this->activeFilters(),
                'filters' => $this->activeFilters(),
                'notify_new_places' => $validated['notifyNewMatches'],
                'notify_price_drop' => $validated['notifyPriceDrop'],
                'notify_available' => $validated['notifyAvailability'],
                'notify_frequency' => 'daily',
                'is_active' => true,
            ]
        );

        $this->resetForm();
        session()->flash('decision-saved-search-status', __('decision.saved.saved_status'));
        unset($this->searches);
    }

    public function edit(int $id): void
    {
        $search = SavedSearch::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->editingId = $search->id;
        $this->name = $search->name;
        $this->cityId = $search->city_id;
        $this->cityQuery = $search->city ?: $search->cityModel?->name ?: '';
        $this->district = $search->district ?: '';
        $this->checkIn = $search->check_in?->toDateString() ?: '';
        $this->checkOut = $search->check_out?->toDateString() ?: '';
        $this->flexibleDates = (bool) $search->flexible_dates;
        $this->priceMin = $search->price_min === null ? '' : (string) $search->price_min;
        $this->priceMax = $search->price_max === null ? '' : (string) $search->price_max;
        $this->currency = $search->currency ?: 'EUR';
        $this->notifyNewMatches = (bool) $search->notify_new_places;
        $this->notifyPriceDrop = (bool) $search->notify_price_drop;
        $this->notifyAvailability = (bool) $search->notify_available;
        $this->filters = [
            ...$this->filters,
            ...collect($search->filters_json ?: [])->map(fn ($value): bool => (bool) $value)->all(),
        ];
    }

    public function delete(int $id): void
    {
        SavedSearch::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        unset($this->searches);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function toggleNotifications(int $id): void
    {
        $search = SavedSearch::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $enabled = ! ($search->notify_new_places || $search->notify_price_drop || $search->notify_available);

        $search->update([
            'notify_new_places' => $enabled,
            'notify_price_drop' => $enabled,
            'notify_available' => $enabled,
        ]);

        unset($this->searches);
    }

    public function runSearch(int $id): void
    {
        $search = SavedSearch::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->redirect(route('search.index', array_filter([
            'locale' => app()->getLocale(),
            'city' => $search->city_id,
            'district' => $search->district,
            'in' => $search->check_in?->toDateString(),
            'out' => $search->check_out?->toDateString(),
            'price_min' => $search->price_min,
            'price_max' => $search->price_max,
            'currency' => $search->currency,
            ...($search->filters_json ?: []),
        ])), navigate: true);
    }

    #[Computed]
    public function cityOptions(): Collection
    {
        $normalized = GeoNameNormalizer::normalize($this->cityQuery);

        if (Str::length($normalized) < 2 || $this->cityId !== null) {
            return collect();
        }

        return City::query()
            ->select(['id', 'name', 'ascii_name', 'population', 'name_normalized', 'status', 'is_active'])
            ->visible()
            ->namePrefix($normalized)
            ->orderByDesc('population')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function searches(): Collection
    {
        return SavedSearch::query()
            ->select([
                'id',
                'user_id',
                'city_id',
                'locale',
                'name',
                'city',
                'district',
                'check_in',
                'check_out',
                'flexible_dates',
                'nights',
                'price_min',
                'price_max',
                'currency',
                'filters_json',
                'notify_new_places',
                'notify_price_drop',
                'notify_available',
                'is_active',
                'created_at',
            ])
            ->where('user_id', auth()->id())
            ->with('cityModel:id,name')
            ->latest()
            ->limit(30)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-searches-list', [
            'searches' => $this->searches,
            'cityOptions' => $this->cityOptions,
        ])->layout('layouts.app', [
            'title' => __('decision.saved.title'),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->cityId = null;
        $this->cityQuery = '';
        $this->district = '';
        $this->checkIn = '';
        $this->checkOut = '';
        $this->flexibleDates = false;
        $this->priceMin = '';
        $this->priceMax = '';
        $this->currency = 'EUR';
        $this->notifyNewMatches = true;
        $this->notifyPriceDrop = true;
        $this->notifyAvailability = true;
        $this->filters = [
            'wifi' => false,
            'kitchen' => false,
            'locker' => false,
            'quiet_hours' => false,
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function activeFilters(): array
    {
        return collect($this->filters)
            ->filter()
            ->map(fn (): bool => true)
            ->all();
    }

    private function nights(?string $checkIn, ?string $checkOut): ?int
    {
        if (! $checkIn || ! $checkOut) {
            return null;
        }

        return (int) CarbonImmutable::parse($checkIn)->diffInDays(CarbonImmutable::parse($checkOut));
    }
}
