# Geo Data Sources

Normal user search must use offline SQLite data. Do not call external geo APIs while a user types, filters, or searches.

Geo country/city search follows the selected interface locale. If the interface is Russian, the autocomplete searches Russian `country_translations` / `city_translations` rows and displays Russian names when available. Future languages must use the same `locale` table design; do not add new language-specific columns or code branches.

## Default Demo Geography

The default `GeoSeeder` is deliberately lightweight. It loads only 2 countries and 5 cities so local demos, tests, and `php artisan app:demo-reset` stay fast:

- Lithuania: Vilnius, Kaunas, Klaipeda
- Germany: Berlin, Munich

Full GeoNames datasets are opt-in through `config/geo.php` and local `.env` values. Testing stays protected from accidental large downloads, while local development may enable full imports through `php artisan migrate:fresh --seed`.

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

Full GeoNames seed path:

```bash
php artisan geo:seed-geonames
php artisan geo:seed-geonames --download-only
php artisan migrate:fresh --seed
```

When `GEONAMES_SEED_ENABLED=true`, `DatabaseSeeder` downloads/prepares configured GeoNames files if needed and imports them during `php artisan migrate:fresh --seed`. The current local full mode uses `allCountries` plus `alternateNamesV2` so city and country lookup can search translated names for every language code available in GeoNames.

The legacy CSV importer accepts common headers including `iso2`, `code`, `alpha-2`, `iso3`, `alpha-3`, `name`, `name_en`, `name_ru`, `name_native`, `phone_code`, `currency_code`, `timezone_default`, and `status`. New multilingual imports must write language-specific names to `country_translations.locale` rather than adding more columns.

Stored fields:

- `iso2`
- `iso3`
- canonical `name`
- legacy/source compatibility fields such as `name_en`, `name_ru`, and `name_native`
- multilingual rows in `country_translations`
- `phone_code`
- `currency_code`
- `timezone_default`
- `status`
- `name_normalized`

Country translations are stored in `country_translations`:

- `country_id`
- `locale`
- `name`
- `name_normalized`
- GeoNames source metadata and alternate-name flags

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
- multilingual rows in `city_translations`
- `status`

GeoNames data requires attribution under its license. Keep the source file name and update date in release notes or import run logs when production imports are performed.

City translations are stored in `city_translations` with `city_id`, `locale`, `name`, `name_normalized`, source metadata, and GeoNames alternate-name flags. Pseudo alternate-name rows such as links, postal codes, IATA codes, and other non-language identifiers are skipped by requiring a real language-like locale code.

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
- Search translated place names for the selected interface locale; display that same locale and fall back only when a translated row does not exist.
- Do not use language-specific columns for new languages; use `country_translations.locale` and `city_translations.locale`.
- Use SQLite indexes on normalized names, status, country filters, and GeoNames IDs.
- Never load the complete country or city list into a mobile select.
- Advanced place filters for district, street, landmark, nearby transit, airports, universities, hospitals, sea, parks, shopping, gyms, coworking, nightlife, and area type must use stored local metadata or offline/imported point data.
- Do not call a live external geocoding/search API while the user searches or filters.
- `near_work` must use a user-saved coordinate/local area reference or a local landmark search; never send a private work address to public Nominatim during normal search.
- If a future `location_points` import is added, store source, license, normalized name, category, country/city IDs, latitude, longitude, and attribution notes.

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

- `storage/app/geo/geonames/countryInfo.txt`
- `storage/app/geo/geonames/allCountries.zip`
- `storage/app/geo/geonames/allCountries.txt`
- `storage/app/geo/geonames/alternateNamesV2.zip`
- `storage/app/geo/geonames/alternateNamesV2.txt`

Large geo imports must run from Artisan, a seeder, or a queued job, never from a web request. In this project the full import is intentionally opt-in through `GEONAMES_SEED_ENABLED=true` so one local command can rebuild everything while automated tests remain lightweight.
