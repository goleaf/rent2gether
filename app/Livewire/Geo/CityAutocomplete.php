<?php

namespace App\Livewire\Geo;

use App\Models\City;
use App\Services\Geo\GeoSearchService;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CityAutocomplete extends Component
{
    public string $query = '';

    public ?int $selectedCityId = null;

    public bool $isOpen = false;

    public string $inputId = 'city-autocomplete';

    public function updatedQuery(): void
    {
        $this->selectedCityId = null;
        $this->isOpen = true;
    }

    #[Computed]
    public function hasEnoughCharacters(): bool
    {
        return Str::length(GeoNameNormalizer::normalize($this->query)) >= 2;
    }

    #[Computed]
    public function results(): array
    {
        if (! $this->isOpen || ! $this->hasEnoughCharacters) {
            return [];
        }

        return app(GeoSearchService::class)
            ->cities($this->query, app()->getLocale())
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->localizedName(),
                'country' => $city->country?->localizedName(),
                'population' => $city->population,
            ])
            ->values()
            ->all();
    }

    public function selectCity(int $cityId): void
    {
        $city = City::query()
            ->select(['id', 'name', 'status', 'is_active'])
            ->visible()
            ->find($cityId);

        if (! $city) {
            return;
        }

        $this->selectedCityId = $city->id;
        $this->query = $city->localizedName();
        $this->isOpen = false;

        $this->dispatch('city-selected', cityId: $city->id, label: $city->localizedName());
    }

    public function render(): View
    {
        return view('livewire.geo.city-autocomplete', [
            'results' => $this->results,
        ]);
    }
}
