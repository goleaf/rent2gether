<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('geo:rebuild-search-index')]
#[Description('Rebuild normalized geo search fields')]
class RebuildGeoSearchIndex extends Command
{
    public function handle(): int
    {
        $countries = 0;
        $regions = 0;
        $cities = 0;

        Country::query()
            ->select(['id', 'iso2', 'code', 'iso3', 'name', 'status', 'is_active', 'name_normalized'])
            ->chunkById(500, function ($items) use (&$countries): void {
                foreach ($items as $country) {
                    $country->name_normalized = GeoNameNormalizer::normalize($country->name);
                    $country->save();
                    $countries++;
                }
            });

        Region::query()
            ->select(['id', 'name', 'name_normalized'])
            ->chunkById(500, function ($items) use (&$regions): void {
                foreach ($items as $region) {
                    $region->name_normalized = GeoNameNormalizer::normalize($region->name);
                    $region->save();
                    $regions++;
                }
            });

        City::query()
            ->select(['id', 'geoname_id', 'source_id', 'name', 'ascii_name', 'status', 'is_active', 'name_normalized'])
            ->chunkById(500, function ($items) use (&$cities): void {
                foreach ($items as $city) {
                    $city->name_normalized = GeoNameNormalizer::normalize($city->ascii_name ?: $city->name);
                    $city->save();
                    $cities++;
                }
            });

        $this->components->info(__('app.geo_import.search_index_rebuilt', [
            'countries' => $countries,
            'regions' => $regions,
            'cities' => $cities,
        ]));

        return self::SUCCESS;
    }
}
