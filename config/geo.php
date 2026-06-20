<?php

return [
    'geonames' => [
        'seed_enabled' => (bool) env('GEONAMES_SEED_ENABLED', false),
        'download_enabled' => (bool) env('GEONAMES_DOWNLOAD_ENABLED', true),
        'import_alternate_names' => (bool) env('GEONAMES_IMPORT_ALTERNATE_NAMES', true),
        'base_url' => env('GEONAMES_BASE_URL', 'https://download.geonames.org/export/dump'),
        'storage_path' => env('GEONAMES_STORAGE_PATH', storage_path('app/geo/geonames')),
        'dataset' => env('GEONAMES_DATASET', 'allCountries'),
        'feature_class' => env('GEONAMES_FEATURE_CLASS', 'P'),
        'languages' => env('GEONAMES_LANGUAGES', 'all'),
        'canonical_locale' => env('GEONAMES_CANONICAL_LOCALE', config('localization.fallback_locale')),
        'chunk_size' => (int) env('GEONAMES_IMPORT_CHUNK_SIZE', 1000),
    ],
];
