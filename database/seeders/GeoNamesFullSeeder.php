<?php

namespace Database\Seeders;

use App\Actions\Geo\SeedGeonamesDataAction;
use Illuminate\Database\Seeder;

class GeoNamesFullSeeder extends Seeder
{
    public function run(SeedGeonamesDataAction $seedGeonamesData): void
    {
        if (! (bool) config('geo.geonames.seed_enabled')) {
            return;
        }

        $result = $seedGeonamesData->handle(
            report: fn (string $key, array $replace = []): null => $this->infoLine($key, $replace),
        );

        $this->command?->info(__('app.geo_import.geonames_seed_complete', $result));
    }

    /**
     * @param  array<string, string|int>  $replace
     */
    private function infoLine(string $key, array $replace): null
    {
        $this->command?->info(__('app.geo_import.'.$key, $replace));

        return null;
    }
}
