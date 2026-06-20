<?php

namespace App\Livewire\Geo;

use App\Models\City;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        $normalized = GeoNameNormalizer::normalize($this->query);

        if (! $this->isOpen || Str::length($normalized) < 2) {
            return [];
        }

        $prefixMatches = $this->baseQuery()
            ->namePrefix($normalized)
            ->orderByDesc('population')
            ->limit(10)
            ->get();

        $remaining = 10 - $prefixMatches->count();
        $matches = $prefixMatches;

        if ($remaining > 0) {
            $containsMatches = $this->baseQuery()
                ->nameContains($normalized)
                ->whereNotIn('id', $prefixMatches->pluck('id'))
                ->orderByDesc('population')
                ->limit($remaining)
                ->get();

            $matches = $matches->concat($containsMatches);
        }

        return $matches
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
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
        $this->query = $city->name;
        $this->isOpen = false;

        $this->dispatch('city-selected', cityId: $city->id, label: $city->name);
    }

    public function render(): View
    {
        return view('livewire.geo.city-autocomplete');
    }

    private function baseQuery(): Builder
    {
        return City::query()
            ->select(['id', 'country_id', 'name', 'ascii_name', 'name_normalized', 'population', 'status', 'is_active'])
            ->with(['country:id,iso2,code,name,name_en,name_ru'])
            ->visible();
    }
}
