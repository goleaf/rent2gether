<?php

namespace App\Services\Geo;

use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GeoSearchService
{
    /**
     * @return EloquentCollection<int, Country>
     */
    public function countries(string $query, ?string $locale = null, int $limit = 10): EloquentCollection
    {
        $normalized = GeoNameNormalizer::normalize($query);

        if (mb_strlen($normalized) < 2) {
            return new EloquentCollection;
        }

        $locale = CountryTranslation::normalizeLocale($locale ?: app()->getLocale());
        $matches = new EloquentCollection;

        foreach ([[$normalized.'%', true], [$normalized.'%', false], ['%'.$normalized.'%', true], ['%'.$normalized.'%', false]] as [$pattern, $translated]) {
            if ($matches->count() >= $limit) {
                break;
            }

            $matches = $matches->merge($this->countryMatches(
                pattern: $pattern,
                locale: $locale,
                translated: $translated,
                limit: $limit - $matches->count(),
                excludeIds: $matches->modelKeys(),
            ));
        }

        return $matches;
    }

    /**
     * @return EloquentCollection<int, City>
     */
    public function cities(string $query, ?string $locale = null, int $limit = 10, ?int $countryId = null): EloquentCollection
    {
        $normalized = GeoNameNormalizer::normalize($query);

        if (mb_strlen($normalized) < 2) {
            return new EloquentCollection;
        }

        $locale = CountryTranslation::normalizeLocale($locale ?: app()->getLocale());
        $matches = new EloquentCollection;

        foreach ([[$normalized.'%', true], [$normalized.'%', false], ['%'.$normalized.'%', true], ['%'.$normalized.'%', false]] as [$pattern, $translated]) {
            if ($matches->count() >= $limit) {
                break;
            }

            $matches = $matches->merge($this->cityMatches(
                pattern: $pattern,
                locale: $locale,
                translated: $translated,
                limit: $limit - $matches->count(),
                countryId: $countryId,
                excludeIds: $matches->modelKeys(),
            ));
        }

        return $matches;
    }

    private function countryQuery(string $locale): Builder
    {
        $countriesTable = (new Country)->getTable();

        return Country::query()
            ->select([
                "{$countriesTable}.id",
                "{$countriesTable}.iso2",
                "{$countriesTable}.code",
                "{$countriesTable}.name",
                "{$countriesTable}.name_normalized",
                "{$countriesTable}.status",
                "{$countriesTable}.is_active",
            ])
            ->visible()
            ->translated($locale);
    }

    private function cityQuery(string $locale, ?int $countryId): Builder
    {
        $citiesTable = (new City)->getTable();

        return City::query()
            ->select([
                "{$citiesTable}.id",
                "{$citiesTable}.country_id",
                "{$citiesTable}.region_id",
                "{$citiesTable}.name",
                "{$citiesTable}.ascii_name",
                "{$citiesTable}.name_normalized",
                "{$citiesTable}.population",
                "{$citiesTable}.status",
                "{$citiesTable}.is_active",
            ])
            ->with([
                'country' => fn ($query) => $query
                    ->select(['id', 'iso2', 'code', 'name'])
                    ->translated($locale),
                'region:id,name',
            ])
            ->visible()
            ->translated($locale)
            ->when($countryId, fn (Builder $query): Builder => $query->where("{$citiesTable}.country_id", $countryId));
    }

    /**
     * @param  list<int>  $excludeIds
     * @return EloquentCollection<int, Country>
     */
    private function countryMatches(string $pattern, string $locale, bool $translated, int $limit, array $excludeIds): EloquentCollection
    {
        $countriesTable = (new Country)->getTable();

        if ($translated) {
            $translationsTable = (new CountryTranslation)->getTable();

            return $this->countryQuery($locale)
                ->join($translationsTable, "{$translationsTable}.country_id", '=', "{$countriesTable}.id")
                ->where("{$translationsTable}.locale", $locale)
                ->where("{$translationsTable}.name_normalized", 'like', $pattern)
                ->when($excludeIds !== [], fn (Builder $query): Builder => $query->whereNotIn("{$countriesTable}.id", $excludeIds))
                ->distinct()
                ->orderBy("{$countriesTable}.name_normalized")
                ->limit($limit)
                ->get();
        }

        return $this->countryQuery($locale)
            ->when($excludeIds !== [], fn (Builder $query): Builder => $query->whereNotIn("{$countriesTable}.id", $excludeIds))
            ->where("{$countriesTable}.name_normalized", 'like', $pattern)
            ->orderBy('name_normalized')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  list<int>  $excludeIds
     * @return EloquentCollection<int, City>
     */
    private function cityMatches(string $pattern, string $locale, bool $translated, int $limit, ?int $countryId, array $excludeIds): EloquentCollection
    {
        $citiesTable = (new City)->getTable();

        if ($translated) {
            $translationsTable = (new CityTranslation)->getTable();

            return $this->cityQuery($locale, $countryId)
                ->join($translationsTable, "{$translationsTable}.city_id", '=', "{$citiesTable}.id")
                ->where("{$translationsTable}.locale", $locale)
                ->where("{$translationsTable}.name_normalized", 'like', $pattern)
                ->when($excludeIds !== [], fn (Builder $query): Builder => $query->whereNotIn("{$citiesTable}.id", $excludeIds))
                ->distinct()
                ->orderByDesc("{$citiesTable}.population")
                ->limit($limit)
                ->get();
        }

        return $this->cityQuery($locale, $countryId)
            ->when($excludeIds !== [], fn (Builder $query): Builder => $query->whereNotIn("{$citiesTable}.id", $excludeIds))
            ->where("{$citiesTable}.name_normalized", 'like', $pattern)
            ->orderByDesc("{$citiesTable}.population")
            ->limit($limit)
            ->get();
    }
}
