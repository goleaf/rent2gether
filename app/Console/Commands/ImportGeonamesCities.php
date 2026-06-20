<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('geo:import-geonames-cities {--source=storage/app/geo/cities1000.txt : Path to a GeoNames cities1000 or allCountries text extract}')]
#[Description('Import GeoNames populated places into SQLite')]
class ImportGeonamesCities extends Command
{
    public function handle(): int
    {
        $source = $this->resolveSourcePath($this->option('source'));

        if ($source === null) {
            $this->components->error(__('app.geo_import.source_missing', [
                'source' => (string) $this->option('source'),
            ]));

            return self::FAILURE;
        }

        $handle = fopen($source, 'rb');

        if ($handle === false) {
            $this->components->error(__('app.geo_import.source_unreadable', [
                'source' => $source,
            ]));

            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        while (($line = fgets($handle)) !== false) {
            $fields = explode("\t", rtrim($line, "\r\n"));

            if (count($fields) < 19) {
                $skipped++;

                continue;
            }

            $country = $this->country($fields[8] ?? '');

            if (! $country) {
                $skipped++;

                continue;
            }

            $geonameId = (int) ($fields[0] ?? 0);

            if ($geonameId < 1) {
                $skipped++;

                continue;
            }

            $region = $this->region($country, $fields[10] ?? null);
            $city = City::query()
                ->where('geoname_id', $geonameId)
                ->orWhere(fn ($query) => $query
                    ->where('source', 'geonames')
                    ->where('source_id', (string) $geonameId))
                ->first();

            $attributes = [
                'geoname_id' => $geonameId,
                'country_id' => $country->id,
                'region_id' => $region?->id,
                'name' => $fields[1] ?: $fields[2],
                'ascii_name' => $fields[2] ?: $fields[1],
                'alternate_names' => $fields[3] ?: null,
                'latitude' => $this->decimal($fields[4] ?? null),
                'longitude' => $this->decimal($fields[5] ?? null),
                'population' => $this->integer($fields[14] ?? null),
                'timezone' => $fields[17] ?: null,
                'feature_class' => $fields[6] ?: null,
                'feature_code' => $fields[7] ?: null,
                'status' => City::STATUS_ACTIVE,
                'source' => 'geonames',
                'source_id' => (string) $geonameId,
                'is_active' => true,
            ];

            $city
                ? $city->fill($attributes)->save()
                : City::query()->create($attributes);

            $imported++;
        }

        fclose($handle);

        $this->components->info(__('app.geo_import.cities_imported', [
            'count' => $imported,
            'skipped' => $skipped,
        ]));

        return self::SUCCESS;
    }

    private function resolveSourcePath(mixed $source): ?string
    {
        if (! is_string($source) || $source === '') {
            return null;
        }

        if (File::exists($source)) {
            return $source;
        }

        $basePath = base_path($source);

        return File::exists($basePath) ? $basePath : null;
    }

    private function country(string $iso2): ?Country
    {
        $iso2 = strtoupper(trim($iso2));

        if ($iso2 === '') {
            return null;
        }

        return Country::query()
            ->where('iso2', $iso2)
            ->orWhere('code', $iso2)
            ->first();
    }

    private function region(Country $country, ?string $admin1Code): ?Region
    {
        $admin1Code = trim((string) $admin1Code);

        if ($admin1Code === '') {
            return null;
        }

        return Region::query()->firstOrCreate(
            [
                'country_id' => $country->id,
                'code' => $admin1Code,
            ],
            [
                'name' => $admin1Code,
                'source' => 'geonames',
                'source_id' => ($country->iso2 ?: $country->code).'.'.$admin1Code,
            ],
        );
    }

    private function decimal(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function integer(?string $value): ?int
    {
        $value = trim((string) $value);

        return $value === '' ? null : (int) $value;
    }
}
