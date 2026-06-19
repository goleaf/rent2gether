---
name: geo-open-data
description: Use when importing countries, regions, cities, coordinates, location search, autocomplete, or map-related datasets from open sources.
---

Use open data and offline imports.

Recommended sources:
- Countries/codes: ISO 3166-compatible dataset.
- Cities/populated places: GeoNames.
- Map/country shapes if needed: Natural Earth.
- Occasional geocoding only: Nominatim/OpenStreetMap with strict rate limits and attribution.

Rules:
- Never hard-code cities manually.
- Do not call external geo APIs during normal user search.
- Import geo data into SQLite.
- Normalize names for fast search.
- Store:
  country code alpha-2
  country code alpha-3 if available
  country name
  localized country names if available
  city name
  city ascii/name_normalized
  latitude
  longitude
  population
  timezone
  GeoNames ID
- City autocomplete must:
  require at least 2 characters
  debounce requests
  limit results
  prioritize exact/prefix match
  prioritize larger population
  return compact payloads
- Add import commands:
  geo:import-countries
  geo:import-geonames-cities
  geo:rebuild-search-index
- Add docs/GEO_DATA_SOURCES.md with source, license, attribution, import steps, update steps.

Для городов лучше использовать GeoNames: он покрывает все страны и более 11 млн географических названий, а также даёт ежедневные extract-файлы вроде allCountries.zip. (geonames.org)
Для картографических слоёв можно использовать Natural Earth, потому что он описан как public domain map dataset. (naturalearthdata.com)
Для Nominatim/OpenStreetMap нельзя делать массовые запросы: публичная политика Nominatim указывает максимум 1 запрос в секунду, обязательный User-Agent/Referer и attribution. (operations.osmfoundation.org)
Для кодов стран можно опираться на ISO 3166, который определяет международные коды стран и подразделений. (ISO)
