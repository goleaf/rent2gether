<?php

namespace App\Livewire\Concerns;

use App\Models\City;
use App\Models\Country;
use App\Services\Geo\GeoSearchService;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait HandlesCountryCityAutocomplete
{
    public string $countryQuery = '';

    public ?int $countryId = null;

    public bool $countrySearchOpen = false;

    public string $cityQuery = '';

    public ?int $cityId = null;

    public bool $citySearchOpen = false;

    public function updatedCountryQuery(): void
    {
        $this->countryId = null;
        $this->cityId = null;
        $this->cityQuery = '';
        $this->countrySearchOpen = true;
        $this->citySearchOpen = false;
    }

    public function updatedCityQuery(): void
    {
        if (! $this->countryId) {
            $this->cityQuery = '';
            $this->cityId = null;
            $this->citySearchOpen = false;

            return;
        }

        $this->cityId = null;
        $this->citySearchOpen = true;
    }

    public function selectCountry(int $countryId): void
    {
        $country = Country::query()
            ->select(['id', 'iso2', 'code', 'name', 'status', 'is_active'])
            ->visible()
            ->translated(app()->getLocale())
            ->find($countryId);

        if (! $country) {
            return;
        }

        $this->countryId = $country->id;
        $this->countryQuery = $country->localizedName();
        $this->countrySearchOpen = false;
        $this->cityId = null;
        $this->cityQuery = '';
        $this->citySearchOpen = false;

        $this->afterCountrySelected($country);
    }

    public function selectCity(int $cityId): void
    {
        if (! $this->countryId) {
            $this->cityQuery = '';

            return;
        }

        $city = City::query()
            ->select(['id', 'country_id', 'region_id', 'name', 'status', 'is_active'])
            ->with(['region:id,name'])
            ->visible()
            ->translated(app()->getLocale())
            ->where('country_id', $this->countryId)
            ->find($cityId);

        if (! $city) {
            return;
        }

        $this->cityId = $city->id;
        $this->cityQuery = $city->localizedName();
        $this->citySearchOpen = false;

        $this->afterCitySelected($city);
    }

    #[Computed]
    public function countryResults(): array
    {
        if (! $this->countrySearchOpen || ! $this->countryQueryHasEnoughCharacters) {
            return [];
        }

        return app(GeoSearchService::class)
            ->countries($this->countryQuery, app()->getLocale())
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->localizedName(),
                'code' => $country->iso2 ?: $country->code,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function cityResults(): array
    {
        if ($this->cityAutocompleteDisabled || ! $this->citySearchOpen || ! $this->cityQueryHasEnoughCharacters) {
            return [];
        }

        return app(GeoSearchService::class)
            ->cities($this->cityQuery, app()->getLocale(), countryId: $this->countryId)
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->localizedName(),
                'country' => $city->country?->localizedName(),
                'region' => $city->region?->name,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function countryQueryHasEnoughCharacters(): bool
    {
        return Str::length(GeoNameNormalizer::normalize($this->countryQuery)) >= 2;
    }

    #[Computed]
    public function cityQueryHasEnoughCharacters(): bool
    {
        return Str::length(GeoNameNormalizer::normalize($this->cityQuery)) >= 2;
    }

    #[Computed]
    public function cityAutocompleteDisabled(): bool
    {
        return ! $this->countryId;
    }

    protected function selectedAutocompleteCountry(): ?Country
    {
        return $this->countryId
            ? Country::query()->select(['id', 'iso2', 'code', 'name'])->translated(app()->getLocale())->find($this->countryId)
            : null;
    }

    protected function selectedAutocompleteCity(): ?City
    {
        return $this->cityId
            ? City::query()->select(['id', 'country_id', 'region_id', 'name', 'latitude', 'longitude'])->with('region')->translated(app()->getLocale())->find($this->cityId)
            : null;
    }

    protected function setCountryCityAutocomplete(?Country $country, ?City $city, ?string $countryFallback = null, ?string $cityFallback = null): void
    {
        $this->countryId = $country?->id;
        $this->countryQuery = $country?->localizedName() ?: (string) $countryFallback;
        $this->cityId = $city?->id;
        $this->cityQuery = $city?->localizedName() ?: (string) $cityFallback;
    }

    protected function resolveCountryCityAutocompleteIdsFromQueries(): void
    {
        if (! $this->countryId && $this->countryQuery !== '') {
            $normalizedCountry = GeoNameNormalizer::normalize($this->countryQuery);

            $country = Country::query()
                ->select(['id', 'iso2', 'code', 'name', 'status', 'is_active'])
                ->visible()
                ->translated(app()->getLocale())
                ->where(fn ($query) => $query
                    ->where('name_normalized', $normalizedCountry)
                    ->orWhere('iso2', strtoupper($this->countryQuery))
                    ->orWhere('code', strtoupper($this->countryQuery)))
                ->first();

            if ($country) {
                $this->countryId = $country->id;
                $this->countryQuery = $country->localizedName();
            }
        }

        if ($this->countryId && ! $this->cityId && $this->cityQuery !== '') {
            $normalizedCity = GeoNameNormalizer::normalize($this->cityQuery);

            $city = City::query()
                ->select(['id', 'country_id', 'region_id', 'name', 'ascii_name', 'status', 'is_active'])
                ->visible()
                ->translated(app()->getLocale())
                ->where('country_id', $this->countryId)
                ->where(fn ($query) => $query
                    ->where('name_normalized', $normalizedCity)
                    ->orWhere('ascii_name', $this->cityQuery)
                    ->orWhere('name', $this->cityQuery))
                ->first();

            if ($city) {
                $this->cityId = $city->id;
                $this->cityQuery = $city->localizedName();
            }
        }
    }

    protected function afterCountrySelected(Country $country): void
    {
        //
    }

    protected function afterCitySelected(City $city): void
    {
        //
    }
}
