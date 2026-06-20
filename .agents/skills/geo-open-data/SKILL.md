---
name: geo-open-data
description: Use when importing countries, regions, cities, coordinates, location search, autocomplete, or map-related datasets from open sources.
---

Use open data and offline imports.

Recommended sources:
- Countries/codes: ISO 3166-compatible dataset.
- Country export with extra fields: REST Countries, after reviewing fields and license notes.
- Small country CSV: DataHub country-list, with its ISO licensing note documented before production use.
- Cities/populated places: GeoNames `cities1000` by default.
- Full place catalog: GeoNames `allCountries` only when the product truly needs it.
- Map/country shapes if needed later: Natural Earth.
- Occasional geocoding only: Nominatim/OpenStreetMap with strict rate limits, User-Agent/Referer, and attribution.

Rules:
- Never hard-code cities manually.
- Do not call external geo APIs during normal user search.
- Do not load a map on the first search screen.
- Do not bulk-geocode or mass-import addresses through public Nominatim.
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

Для кодов стран можно опираться на ISO 3166, который определяет международные коды стран и подразделений. (ISO)
REST Countries удобно использовать как экспорт стран с дополнительными полями, потому что он собирает данные из публичных реестров и open-data источников. (REST Countries)
DataHub country-list подходит как маленький CSV для ISO 3166-1 alpha-2, но его ISO-лицензионную оговорку надо описывать в docs/GEO_DATA_SOURCES.md. (datahub.io)
Для городов лучше использовать GeoNames cities1000; GeoNames allCountries нужен только если действительно требуется полный каталог мест. GeoNames покрывает все страны и даёт daily extracts. (geonames.org)
Для первой версии не грузить карту на первом экране поиска. Для картографических слоёв можно использовать Natural Earth, потому что это public domain map dataset. (naturalearthdata.com)
Для Nominatim/OpenStreetMap нельзя делать массовые запросы или массовый импорт адресов: публичная политика Nominatim указывает максимум 1 запрос в секунду, обязательный User-Agent/Referer и attribution. (operations.osmfoundation.org)
