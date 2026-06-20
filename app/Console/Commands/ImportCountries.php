<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('geo:import-countries {--source=storage/app/geo/countries.csv : Path to an ISO 3166-compatible countries CSV file}')]
#[Description('Import ISO 3166-compatible countries into SQLite')]
class ImportCountries extends Command
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

        $headers = $this->readHeaders($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $record = $this->record($headers, $row);
            $iso2 = strtoupper((string) $this->value($record, ['iso2', 'code', 'alpha2', 'alpha_2', 'country_code', 'cca2']));
            $nameEn = $this->value($record, ['name_en', 'name', 'country', 'english_name', 'official_name']);

            if ($iso2 === '' || $nameEn === null) {
                $skipped++;

                continue;
            }

            $country = Country::query()
                ->where('iso2', $iso2)
                ->orWhere('code', $iso2)
                ->first();

            $iso3 = strtoupper((string) $this->value($record, ['iso3', 'alpha3', 'alpha_3', 'cca3']));
            $currencyCode = strtoupper((string) $this->value($record, ['currency_code', 'currency']));
            $status = str((string) ($this->value($record, ['status']) ?: Country::STATUS_ACTIVE))->lower()->toString();

            $attributes = [
                'iso2' => $iso2,
                'code' => $iso2,
                'iso3' => $iso3 === '' ? null : $iso3,
                'name_en' => $nameEn,
                'name_ru' => $this->value($record, ['name_ru', 'ru', 'russian_name']),
                'name_native' => $this->value($record, ['name_native', 'native_name']),
                'name' => $nameEn,
                'currency_code' => $currencyCode === '' ? null : $currencyCode,
                'phone_code' => $this->value($record, ['phone_code', 'calling_code']),
                'timezone_default' => $this->value($record, ['timezone_default', 'timezone']),
                'status' => $status,
                'source' => $this->value($record, ['source']) ?: 'iso_3166',
                'is_active' => $status === Country::STATUS_ACTIVE,
            ];

            $country
                ? $country->fill($attributes)->save()
                : Country::query()->create($attributes);

            $imported++;
        }

        fclose($handle);

        $this->components->info(__('app.geo_import.countries_imported', [
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

    /**
     * @param  resource  $handle
     * @return list<string>
     */
    private function readHeaders($handle): array
    {
        $headers = fgetcsv($handle);

        return collect($headers ?: [])
            ->map(fn (?string $header): string => $this->normalizeHeader($header))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string|null>  $row
     * @return array<string, string|null>
     */
    private function record(array $headers, array $row): array
    {
        $record = [];

        foreach ($headers as $index => $header) {
            $record[$header] = $row[$index] ?? null;
        }

        return $record;
    }

    private function normalizeHeader(?string $header): string
    {
        return str($header ?? '')
            ->replace("\u{feff}", '')
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', '_')
            ->trim('_')
            ->toString();
    }

    /**
     * @param  array<string, string|null>  $record
     * @param  list<string>  $keys
     */
    private function value(array $record, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($record[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
