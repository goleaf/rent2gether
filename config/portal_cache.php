<?php

return [
    'enabled' => env('PORTAL_PAGE_CACHE_ENABLED', true),
    'store' => env('PORTAL_PAGE_CACHE_STORE', 'database'),
    'ttl_seconds' => (int) env('PORTAL_PAGE_CACHE_TTL', 300),
    'private_ttl_seconds' => (int) env('PORTAL_PRIVATE_PAGE_CACHE_TTL', 120),
    'max_response_bytes' => (int) env('PORTAL_PAGE_CACHE_MAX_BYTES', 1048576),
    'cache_guest_pages' => env('PORTAL_PAGE_CACHE_GUESTS', true),
    'cache_authenticated_pages' => env('PORTAL_PAGE_CACHE_AUTHENTICATED', true),
    'respect_response_no_store' => env('PORTAL_PAGE_CACHE_RESPECT_NO_STORE', false),
    'header_name' => 'X-Portal-Cache',
    'ignored_query_parameters' => [
        'fbclid',
        'gclid',
        'utm_*',
    ],
    'excluded_path_prefixes' => [
        'livewire',
        '_debugbar',
    ],
    'excluded_route_names' => [
        'livewire.*',
    ],
];
