<?php

namespace App\Console\Commands;

use App\Actions\Geo\SeedGeonamesDataAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('geo:seed-geonames
    {--download-only : Download and extract configured GeoNames files without importing rows}
    {--skip-download : Use existing local files only}
    {--dataset= : Override the configured GeoNames dataset, for example allCountries or cities1000}
    {--storage= : Override the configured local GeoNames storage path}')]
#[Description('Download, prepare, and import GeoNames countries, populated places, and multilingual alternate names')]
class SeedGeonamesData extends Command
{
    public function handle(SeedGeonamesDataAction $seedGeonamesData): int
    {
        $options = [
            'download_only' => (bool) $this->option('download-only'),
            'download_enabled' => ! (bool) $this->option('skip-download') && (bool) config('geo.geonames.download_enabled'),
        ];

        if (is_string($this->option('dataset')) && $this->option('dataset') !== '') {
            $options['dataset'] = $this->option('dataset');
        }

        if (is_string($this->option('storage')) && $this->option('storage') !== '') {
            $options['storage_path'] = $this->option('storage');
        }

        $result = $seedGeonamesData->handle(
            $options,
            fn (string $key, array $replace = []): null => $this->infoLine($key, $replace),
        );

        $messageKey = $options['download_only']
            ? 'geonames_download_complete'
            : 'geonames_seed_complete';

        $this->components->info(__('app.geo_import.'.$messageKey, $result));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string|int>  $replace
     */
    private function infoLine(string $key, array $replace): null
    {
        $this->components->info(__('app.geo_import.'.$key, $replace));

        return null;
    }
}
