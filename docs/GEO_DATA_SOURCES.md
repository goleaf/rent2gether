# Geo Data Sources

Normal search must use offline SQLite data. Do not call an external geography API while a user types.

## Countries and codes

Use ISO 3166-compatible alpha-2 codes as the stable country identifier and retain alpha-3 where available.

- Source: [ISO 3166 country codes](https://www.iso.org/iso-3166-country-codes.html)
- Update policy: review ISO Maintenance Agency changes before each country import release
- Storage: country codes, default name, active state, and localized names in translation rows

ISO permits free use of the codes, but imported country names must retain the licensing terms of the selected distributable dataset.

## Cities and populated places

Use the [GeoNames daily export](https://download.geonames.org/export/dump/) under CC BY 4.0.

Default to `cities500.zip` for a practical populated-place search corpus. Use country extracts or `allCountries.zip` only when the broader feature set is required.

Store at least:

- GeoNames ID
- country alpha-2 code
- canonical and ASCII names
- normalized search name
- latitude and longitude
- feature code
- population
- timezone
- modification date

Display GeoNames attribution wherever imported data is surfaced, and retain the import source/version in metadata.

## Map shapes

Use [Natural Earth](https://www.naturalearthdata.com/about/terms-of-use/) only when country or regional shapes are needed. Its raster and vector map data are public domain. Do not add map data or a map library to the first-load bundle before a real map feature exists.

## Occasional geocoding

Public [Nominatim usage policy](https://operations.osmfoundation.org/policies/nominatim/) allows only light use and sets an absolute maximum of one request per second. It also requires an identifying User-Agent or Referer and OpenStreetMap attribution.

- Never bulk-geocode through the public Nominatim service.
- Cache occasional results and make the provider replaceable.
- Queue requests, enforce the rate limit, and provide attribution.
- Use a self-hosted or contracted service for sustained production volume.

## Import workflow

Planned commands are:

```text
geo:import-countries
geo:import-geonames-cities
geo:rebuild-search-index
```

Each import must download to temporary storage, verify the expected format, import in chunks, rebuild normalized search fields, and report source/version metadata. Large geo imports must not run from a web request or default seeder.

## Search contract

- Require at least two normalized characters.
- Debounce by 500ms or more.
- Return at most ten compact results.
- Rank exact and prefix matches first, then larger population.
- Index the normalized prefix search and country/region filters used by the UI.
