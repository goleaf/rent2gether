# Geo Data Sources

Normal user search must use offline SQLite data. Do not call external geo APIs while a user types, filters, or searches.

## Default Demo Geography

The default `GeoSeeder` is deliberately lightweight. It loads only 2 countries and 5 cities so local demos, tests, and `php artisan app:demo-reset` stay fast:

- Lithuania: Vilnius, Kaunas, Klaipeda
- Germany: Berlin, Munich

Do not add full GeoNames datasets to the default seeder. Use the import commands below for larger offline datasets.

## Countries

Use an ISO 3166-compatible country source. ISO 3166 is the baseline for internationally recognized country and subdivision codes, while the imported dataset must be reviewed for redistribution terms before it is committed or used in production.

Accepted inputs:

- ISO 3166-compatible CSV downloaded to `storage/app/geo/countries.csv`
- [REST Countries](https://restcountries.com/) export when extra fields are needed. REST Countries aggregates country data from public registries and open-data projects, so it is convenient for `iso2`, `iso3`, names, currencies, time zones, and phone codes.
- [DataHub country-list](https://datahub.io/core/country-list) CSV for a small `name` and ISO 3166-1 alpha-2 `code` file. Use it carefully because the dataset has an ISO-related licensing note and only covers the minimal country-name/code pair.

Import:

```bash
php artisan geo:import-countries --source=storage/app/geo/countries.csv
```

The importer accepts common headers including `iso2`, `code`, `alpha-2`, `iso3`, `alpha-3`, `name`, `name_en`, `name_ru`, `name_native`, `phone_code`, `currency_code`, `timezone_default`, and `status`.

Stored fields:

- `iso2`
- `iso3`
- `name_en`
- `name_ru`
- `name_native`
- `phone_code`
- `currency_code`
- `timezone_default`
- `status`
- `name_normalized`

License notes:

- ISO country codes are the stable identifier, but verify the license of the redistributed country-name dataset before importing.
- REST Countries should be treated as a convenient export source, not as an online dependency during normal application use.
- DataHub country-list is published as ODC-PDDL-1.0 and notes that the underlying country list follows ISO 3166-1.

## Cities

Use [GeoNames daily dump files](https://download.geonames.org/export/dump/). The default production import should be `cities1000.zip`, which is a practical fit for city autocomplete because it keeps the local SQLite index useful without importing every small feature. Use `allCountries.zip` only when the product truly needs the full place catalog.

Import:

```bash
php artisan geo:import-geonames-cities --source=storage/app/geo/cities1000.txt
```

The importer expects the standard GeoNames tab-delimited geoname row:

- `geoname_id`
- `name`
- `ascii_name`
- `alternate_names`
- `latitude`
- `longitude`
- `feature_class`
- `feature_code`
- `country_code`
- `admin1_code`
- `population`
- `timezone`

Stored fields:

- `geoname_id`
- `country_id`
- `region_id`
- `name`
- `ascii_name`
- `alternate_names`
- `latitude`
- `longitude`
- `population`
- `timezone`
- `feature_class`
- `feature_code`
- `name_normalized`
- `status`

GeoNames data requires attribution under its license. Keep the source file name and update date in release notes or import run logs when production imports are performed.

## Search Index

Rebuild normalized country, region, and city search fields after imports or manual data correction:

```bash
php artisan geo:rebuild-search-index
```

Search rules:

- Require at least 2 normalized characters.
- Debounce autocomplete by 500ms.
- Return at most 10 compact city results.
- Prioritize city prefix matches, then larger population.
- Use SQLite indexes on normalized names, status, country filters, and GeoNames IDs.
- Never load the complete country or city list into a mobile select.

## Map Shapes

Do not load a map on the first search screen. If later features need country boundaries, simple layers, or low-detail map shapes, use [Natural Earth](https://www.naturalearthdata.com/about/terms-of-use/) because its vector and raster map data is published as public domain. Maps are still excluded from the first-load bundle until a real map feature exists.

## Nominatim And OpenStreetMap

Use public [Nominatim](https://operations.osmfoundation.org/policies/nominatim/) only for occasional geocoding with strict limits:

- Never bulk-geocode through public Nominatim.
- Do not call Nominatim during normal search or autocomplete.
- Do not use public Nominatim to mass-import addresses.
- Send a clear User-Agent or Referer.
- Respect the public limit of at most 1 request per second.
- Show OpenStreetMap attribution where OSM data appears.
- Cache occasional results and keep the provider replaceable.

For sustained production geocoding, use a self-hosted or contracted provider.

## Local Files

Recommended local paths:

- `storage/app/geo/countries.csv`
- `storage/app/geo/cities1000.txt`
- `storage/app/geo/allCountries.txt`

Large geo imports must run from Artisan or a queued job, not from a web request or default seeder.
