<?php

namespace App\Actions\Geo;

use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Region;
use App\Support\Geo\GeoNameNormalizer;
use Closure;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class SeedGeonamesDataAction
{
    private const SOURCE_COUNTRY_INFO = 'geonames_country_info';

    private const SOURCE_ALTERNATE_NAMES = 'geonames_alternate_names_v2';

    /** @var array<string, int> */
    private array $regionIds = [];

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, int|string>
     */
    public function handle(array $options = [], ?Closure $report = null): array
    {
        $files = $this->prepareFiles($options, $report);

        if ((bool) ($options['download_only'] ?? false)) {
            return [
                'countries' => 0,
                'cities' => 0,
                'country_translations' => 0,
                'city_translations' => 0,
                'skipped' => 0,
                'storage_path' => $files['storage_path'],
            ];
        }

        $this->report($report, 'geonames_import_countries');
        $countryResult = $this->importCountries($files['country_info'], $options);

        $this->report($report, 'geonames_import_cities');
        $cityResult = $this->importCities($files['places'], $options);

        $alternateResult = ['country_translations' => 0, 'city_translations' => 0, 'skipped' => 0];

        if ((bool) ($options['import_alternate_names'] ?? config('geo.geonames.import_alternate_names'))) {
            $this->report($report, 'geonames_import_translations');
            $alternateResult = $this->importAlternateNames($files['alternate_names'], $options);
        }

        return [
            'countries' => $countryResult['countries'],
            'cities' => $cityResult['cities'],
            'country_translations' => $countryResult['country_translations'] + $alternateResult['country_translations'],
            'city_translations' => $alternateResult['city_translations'],
            'skipped' => $countryResult['skipped'] + $cityResult['skipped'] + $alternateResult['skipped'],
            'storage_path' => $files['storage_path'],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{country_info:string,places:string,alternate_names:string,storage_path:string}
     */
    private function prepareFiles(array $options, ?Closure $report): array
    {
        $storagePath = $this->storagePath($options);
        $dataset = $this->dataset($options);

        File::ensureDirectoryExists($storagePath);

        $countryInfo = $storagePath.'/countryInfo.txt';
        $places = $storagePath.'/'.$dataset.'.txt';
        $alternateNames = $storagePath.'/alternateNamesV2.txt';

        $this->ensurePlainFile('countryInfo.txt', $countryInfo, $options, $report);
        $this->ensureZipExtracted($dataset.'.zip', $dataset.'.txt', $places, $options, $report);

        if ((bool) ($options['import_alternate_names'] ?? config('geo.geonames.import_alternate_names'))) {
            $this->ensureZipExtracted('alternateNamesV2.zip', 'alternateNamesV2.txt', $alternateNames, $options, $report);
        }

        return [
            'country_info' => $countryInfo,
            'places' => $places,
            'alternate_names' => $alternateNames,
            'storage_path' => $storagePath,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{countries:int,country_translations:int,skipped:int}
     */
    private function importCountries(string $path, array $options): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException(__('app.geo_import.source_unreadable', ['source' => $path]));
        }

        $now = $this->timestamp();
        $countries = [];
        $canonicalTranslations = [];
        $skipped = 0;
        $canonicalLocale = CountryTranslation::normalizeLocale(
            (string) ($options['canonical_locale'] ?? config('geo.geonames.canonical_locale')),
        );

        while (($line = fgets($handle)) !== false) {
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $fields = explode("\t", rtrim($line, "\r\n"));
            $iso2 = strtoupper(trim((string) ($fields[0] ?? '')));
            $name = trim((string) ($fields[4] ?? ''));
            $geonameId = $this->integer($fields[16] ?? null);

            if ($iso2 === '' || $name === '') {
                $skipped++;

                continue;
            }

            $countries[$iso2] = [
                'geoname_id' => $geonameId,
                'iso2' => $iso2,
                'code' => $iso2,
                'iso3' => strtoupper(trim((string) ($fields[1] ?? ''))) ?: null,
                'name' => $name,
                'name_en' => $name,
                'name_ru' => null,
                'name_native' => null,
                'name_normalized' => GeoNameNormalizer::normalize($name),
                'currency_code' => strtoupper(trim((string) ($fields[10] ?? ''))) ?: null,
                'phone_code' => trim((string) ($fields[12] ?? '')) ?: null,
                'timezone_default' => null,
                'status' => Country::STATUS_ACTIVE,
                'source' => self::SOURCE_COUNTRY_INFO,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($canonicalLocale !== '') {
                $canonicalTranslations[$iso2] = [
                    'locale' => $canonicalLocale,
                    'name' => $name,
                    'name_normalized' => GeoNameNormalizer::normalize($name),
                    'source' => self::SOURCE_COUNTRY_INFO,
                    'source_id' => $geonameId ? (string) $geonameId : null,
                    'is_preferred' => true,
                    'is_short' => false,
                    'is_colloquial' => false,
                    'is_historic' => false,
                    'valid_from' => null,
                    'valid_to' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        fclose($handle);

        if ($countries !== []) {
            Country::query()->upsert(
                array_values($countries),
                ['iso2'],
                [
                    'geoname_id',
                    'code',
                    'iso3',
                    'name',
                    'name_en',
                    'name_ru',
                    'name_native',
                    'name_normalized',
                    'currency_code',
                    'phone_code',
                    'timezone_default',
                    'status',
                    'source',
                    'is_active',
                    'updated_at',
                ],
            );
        }

        $countryIds = Country::query()
            ->whereIn('iso2', array_keys($canonicalTranslations))
            ->pluck('id', 'iso2');

        $translationRows = [];

        foreach ($canonicalTranslations as $iso2 => $translation) {
            $countryId = $countryIds->get($iso2);

            if ($countryId) {
                $translationRows[] = ['country_id' => $countryId] + $translation;
            }
        }

        $this->upsertCountryTranslations($translationRows);

        return [
            'countries' => count($countries),
            'country_translations' => count($translationRows),
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{cities:int,skipped:int}
     */
    private function importCities(string $path, array $options): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException(__('app.geo_import.source_unreadable', ['source' => $path]));
        }

        $countryIds = $this->countryIdsByIso2();
        $chunkSize = $this->chunkSize($options);
        $featureClass = trim((string) ($options['feature_class'] ?? config('geo.geonames.feature_class')));
        $now = $this->timestamp();
        $rows = [];
        $cities = 0;
        $skipped = 0;

        while (($line = fgets($handle)) !== false) {
            $fields = explode("\t", rtrim($line, "\r\n"));

            if (count($fields) < 19) {
                $skipped++;

                continue;
            }

            if ($featureClass !== '' && ($fields[6] ?? '') !== $featureClass) {
                $skipped++;

                continue;
            }

            $geonameId = $this->integer($fields[0] ?? null);
            $countryCode = strtoupper(trim((string) ($fields[8] ?? '')));
            $countryId = $countryIds[$countryCode] ?? null;

            if (! $geonameId || ! $countryId) {
                $skipped++;

                continue;
            }

            $name = trim((string) ($fields[1] ?? ''));
            $asciiName = trim((string) ($fields[2] ?? '')) ?: $name;

            if ($name === '' && $asciiName === '') {
                $skipped++;

                continue;
            }

            $rows[(string) $geonameId] = [
                'geoname_id' => $geonameId,
                'country_id' => $countryId,
                'region_id' => $this->regionId($countryId, $countryCode, $fields[10] ?? null),
                'name' => $name ?: $asciiName,
                'ascii_name' => $asciiName ?: $name,
                'alternate_names' => trim((string) ($fields[3] ?? '')) ?: null,
                'name_normalized' => GeoNameNormalizer::normalize($asciiName ?: $name),
                'latitude' => $this->decimal($fields[4] ?? null),
                'longitude' => $this->decimal($fields[5] ?? null),
                'population' => $this->integer($fields[14] ?? null),
                'timezone' => trim((string) ($fields[17] ?? '')) ?: null,
                'feature_class' => trim((string) ($fields[6] ?? '')) ?: null,
                'feature_code' => trim((string) ($fields[7] ?? '')) ?: null,
                'status' => City::STATUS_ACTIVE,
                'source' => 'geonames',
                'source_id' => (string) $geonameId,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $cities++;

            if (count($rows) >= $chunkSize) {
                $this->upsertCities(array_values($rows));
                $rows = [];
            }
        }

        fclose($handle);

        if ($rows !== []) {
            $this->upsertCities(array_values($rows));
        }

        return [
            'cities' => $cities,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{country_translations:int,city_translations:int,skipped:int}
     */
    private function importAlternateNames(string $path, array $options): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException(__('app.geo_import.source_unreadable', ['source' => $path]));
        }

        $countryIds = $this->countryIdsByGeoname();
        $languageFilter = $this->languageFilter($options);
        $chunkSize = $this->chunkSize($options);
        $countryRows = [];
        $cityCandidates = [];
        $countryTranslations = 0;
        $cityTranslations = 0;
        $skipped = 0;

        while (($line = fgets($handle)) !== false) {
            $fields = explode("\t", rtrim($line, "\r\n"));

            if (count($fields) < 4) {
                $skipped++;

                continue;
            }

            $alternateNameId = trim((string) ($fields[0] ?? ''));
            $geonameId = $this->integer($fields[1] ?? null);
            $locale = $this->alternateNameLocale($fields[2] ?? null);
            $name = trim((string) ($fields[3] ?? ''));

            if (! $geonameId || $locale === null || $name === '' || ! $this->languageAllowed($locale, $languageFilter)) {
                $skipped++;

                continue;
            }

            $payload = $this->translationPayload($locale, $name, $alternateNameId, $fields);
            $countryId = $countryIds[(string) $geonameId] ?? null;

            if ($countryId) {
                $countryRows[] = ['country_id' => $countryId] + $payload;
                $countryTranslations++;
            } else {
                $cityCandidates[] = ['geoname_id' => $geonameId] + $payload;
            }

            if (count($countryRows) >= $chunkSize) {
                $this->upsertCountryTranslations($countryRows);
                $countryRows = [];
            }

            if (count($cityCandidates) >= $chunkSize) {
                $cityTranslations += $this->flushCityTranslations($cityCandidates);
                $cityCandidates = [];
            }
        }

        fclose($handle);

        if ($countryRows !== []) {
            $this->upsertCountryTranslations($countryRows);
        }

        if ($cityCandidates !== []) {
            $cityTranslations += $this->flushCityTranslations($cityCandidates);
        }

        return [
            'country_translations' => $countryTranslations,
            'city_translations' => $cityTranslations,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function upsertCities(array $rows): void
    {
        City::query()->upsert(
            $rows,
            ['geoname_id'],
            [
                'country_id',
                'region_id',
                'name',
                'ascii_name',
                'alternate_names',
                'name_normalized',
                'latitude',
                'longitude',
                'population',
                'timezone',
                'feature_class',
                'feature_code',
                'status',
                'source',
                'source_id',
                'is_active',
                'updated_at',
            ],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function upsertCountryTranslations(array $rows): void
    {
        $rows = $this->uniqueTranslationRows($rows, 'country_id');

        if ($rows === []) {
            return;
        }

        CountryTranslation::query()->upsert(
            $rows,
            ['country_id', 'locale', 'name_normalized'],
            [
                'name',
                'source',
                'source_id',
                'is_preferred',
                'is_short',
                'is_colloquial',
                'is_historic',
                'valid_from',
                'valid_to',
                'updated_at',
            ],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     */
    private function flushCityTranslations(array $candidates): int
    {
        $geonameIds = array_values(array_unique(array_map(
            fn (array $candidate): int => (int) $candidate['geoname_id'],
            $candidates,
        )));

        $cityIds = City::query()
            ->whereIn('geoname_id', $geonameIds)
            ->pluck('id', 'geoname_id');

        $rows = [];

        foreach ($candidates as $candidate) {
            $cityId = $cityIds->get((int) $candidate['geoname_id']);

            if (! $cityId) {
                continue;
            }

            unset($candidate['geoname_id']);
            $rows[] = ['city_id' => $cityId] + $candidate;
        }

        $rows = $this->uniqueTranslationRows($rows, 'city_id');

        if ($rows === []) {
            return 0;
        }

        CityTranslation::query()->upsert(
            $rows,
            ['city_id', 'locale', 'name_normalized'],
            [
                'name',
                'source',
                'source_id',
                'is_preferred',
                'is_short',
                'is_colloquial',
                'is_historic',
                'valid_from',
                'valid_to',
                'updated_at',
            ],
        );

        return count($rows);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function uniqueTranslationRows(array $rows, string $ownerKey): array
    {
        $unique = [];

        foreach ($rows as $row) {
            $nameNormalized = GeoNameNormalizer::normalize((string) ($row['name'] ?? ''));

            if ($nameNormalized === '') {
                continue;
            }

            $row['locale'] = CountryTranslation::normalizeLocale((string) $row['locale']);
            $row['name_normalized'] = $nameNormalized;
            $unique[$row[$ownerKey].'|'.$row['locale'].'|'.$nameNormalized] = $row;
        }

        return array_values($unique);
    }

    /**
     * @param  list<string|null>  $fields
     * @return array<string, mixed>
     */
    private function translationPayload(string $locale, string $name, string $sourceId, array $fields): array
    {
        return [
            'locale' => $locale,
            'name' => $name,
            'name_normalized' => GeoNameNormalizer::normalize($name),
            'source' => self::SOURCE_ALTERNATE_NAMES,
            'source_id' => $sourceId === '' ? null : $sourceId,
            'is_preferred' => ($fields[4] ?? '') === '1',
            'is_short' => ($fields[5] ?? '') === '1',
            'is_colloquial' => ($fields[6] ?? '') === '1',
            'is_historic' => ($fields[7] ?? '') === '1',
            'valid_from' => trim((string) ($fields[8] ?? '')) ?: null,
            'valid_to' => trim((string) ($fields[9] ?? '')) ?: null,
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function countryIdsByIso2(): array
    {
        return Country::query()
            ->select(['id', 'iso2', 'code'])
            ->get()
            ->flatMap(fn (Country $country): array => array_filter([
                strtoupper((string) $country->iso2) => $country->id,
                strtoupper((string) $country->code) => $country->id,
            ]))
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function countryIdsByGeoname(): array
    {
        return Country::query()
            ->whereNotNull('geoname_id')
            ->pluck('id', 'geoname_id')
            ->mapWithKeys(fn (int $id, int|string $geonameId): array => [(string) $geonameId => $id])
            ->all();
    }

    private function regionId(int $countryId, string $countryCode, ?string $admin1Code): ?int
    {
        $admin1Code = trim((string) $admin1Code);

        if ($admin1Code === '') {
            return null;
        }

        $key = $countryId.'|'.$admin1Code;

        if (isset($this->regionIds[$key])) {
            return $this->regionIds[$key];
        }

        $region = Region::query()->firstOrCreate(
            ['country_id' => $countryId, 'code' => $admin1Code],
            [
                'name' => $admin1Code,
                'name_normalized' => GeoNameNormalizer::normalize($admin1Code),
                'source' => 'geonames',
                'source_id' => $countryCode.'.'.$admin1Code,
            ],
        );

        return $this->regionIds[$key] = $region->id;
    }

    private function ensurePlainFile(string $sourceName, string $target, array $options, ?Closure $report): void
    {
        if (File::exists($target) && File::size($target) > 0) {
            return;
        }

        $this->download($sourceName, $target, $options, $report);
    }

    private function ensureZipExtracted(string $zipName, string $entryName, string $target, array $options, ?Closure $report): void
    {
        if (File::exists($target) && File::size($target) > 0) {
            return;
        }

        $zipPath = dirname($target).'/'.$zipName;

        if (! File::exists($zipPath) || File::size($zipPath) < 1) {
            $this->download($zipName, $zipPath, $options, $report);
        }

        $this->extractZip($zipPath, $entryName, $target);
    }

    private function download(string $sourceName, string $target, array $options, ?Closure $report): void
    {
        if (! (bool) ($options['download_enabled'] ?? config('geo.geonames.download_enabled'))) {
            throw new RuntimeException(__('app.geo_import.source_missing', ['source' => $target]));
        }

        $url = rtrim((string) ($options['base_url'] ?? config('geo.geonames.base_url')), '/').'/'.$sourceName;
        $this->report($report, 'geonames_downloading', ['file' => $sourceName]);

        File::ensureDirectoryExists(dirname($target));

        $context = stream_context_create([
            'http' => [
                'timeout' => 120,
                'user_agent' => 'rent2gether-geonames-import/1.0',
            ],
        ]);
        $input = @fopen($url, 'rb', false, $context);

        if ($input === false) {
            throw new RuntimeException(__('app.geo_import.download_failed', ['url' => $url]));
        }

        $temporaryTarget = $target.'.part';
        $output = fopen($temporaryTarget, 'wb');

        if ($output === false) {
            fclose($input);

            throw new RuntimeException(__('app.geo_import.source_unreadable', ['source' => $temporaryTarget]));
        }

        while (! feof($input)) {
            $chunk = fread($input, 1024 * 1024);

            if ($chunk === false) {
                fclose($input);
                fclose($output);

                throw new RuntimeException(__('app.geo_import.download_failed', ['url' => $url]));
            }

            fwrite($output, $chunk);
        }

        fclose($input);
        fclose($output);
        File::move($temporaryTarget, $target);
    }

    private function extractZip(string $zipPath, string $entryName, string $target): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException(__('app.geo_import.zip_unreadable', ['source' => $zipPath]));
        }

        $entryIndex = $zip->locateName($entryName);

        if ($entryIndex === false) {
            $zip->close();

            throw new RuntimeException(__('app.geo_import.zip_entry_missing', [
                'source' => $zipPath,
                'entry' => $entryName,
            ]));
        }

        $input = $zip->getStream($entryName);

        if ($input === false) {
            $zip->close();

            throw new RuntimeException(__('app.geo_import.zip_entry_missing', [
                'source' => $zipPath,
                'entry' => $entryName,
            ]));
        }

        $temporaryTarget = $target.'.part';
        $output = fopen($temporaryTarget, 'wb');

        if ($output === false) {
            fclose($input);
            $zip->close();

            throw new RuntimeException(__('app.geo_import.source_unreadable', ['source' => $temporaryTarget]));
        }

        while (! feof($input)) {
            $chunk = fread($input, 1024 * 1024);

            if ($chunk === false) {
                fclose($input);
                fclose($output);
                $zip->close();

                throw new RuntimeException(__('app.geo_import.zip_unreadable', ['source' => $zipPath]));
            }

            fwrite($output, $chunk);
        }

        fclose($input);
        fclose($output);
        $zip->close();
        File::move($temporaryTarget, $target);
    }

    private function alternateNameLocale(?string $value): ?string
    {
        $locale = CountryTranslation::normalizeLocale($value);

        return preg_match('/^[a-z]{2,3}(?:-[A-Za-z0-9]{2,8}){0,2}$/', $locale) === 1
            ? $locale
            : null;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return list<string>|null
     */
    private function languageFilter(array $options): ?array
    {
        $value = trim((string) ($options['languages'] ?? config('geo.geonames.languages')));

        if ($value === '' || mb_strtolower($value) === 'all') {
            return null;
        }

        return collect(explode(',', $value))
            ->map(fn (string $locale): string => CountryTranslation::normalizeLocale($locale))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>|null  $languageFilter
     */
    private function languageAllowed(string $locale, ?array $languageFilter): bool
    {
        return $languageFilter === null || in_array($locale, $languageFilter, true);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function storagePath(array $options): string
    {
        $path = (string) ($options['storage_path'] ?? config('geo.geonames.storage_path'));

        return str_starts_with($path, '/') ? $path : base_path($path);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function dataset(array $options): string
    {
        $dataset = trim((string) ($options['dataset'] ?? config('geo.geonames.dataset')));

        return $dataset === '' ? 'allCountries' : $dataset;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function chunkSize(array $options): int
    {
        return max(100, (int) ($options['chunk_size'] ?? config('geo.geonames.chunk_size')));
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

    private function timestamp(): string
    {
        return now()->toDateTimeString();
    }

    /**
     * @param  array<string, string|int>  $replace
     */
    private function report(?Closure $report, string $key, array $replace = []): void
    {
        if ($report) {
            $report($key, $replace);
        }
    }
}
