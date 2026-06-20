<?php

return [
    'fields' => [
        'country' => 'Country',
        'city' => 'City',
    ],
    'helpers' => [
        'country' => 'Type at least 2 characters and choose a country from imported open data.',
        'city' => 'Type at least 2 characters. Cities are filtered by the selected country.',
        'city_disabled' => 'Choose a country first, then choose a city.',
    ],
    'placeholders' => [
        'country' => 'Start typing a country',
        'city' => 'Start typing a city',
        'city_disabled' => 'Choose a country first',
    ],
    'loading' => [
        'country' => 'Looking for countries...',
        'city' => 'Looking for cities...',
    ],
    'empty' => [
        'country' => 'No countries found. Check the spelling or import country data first.',
        'city' => 'No cities found for this country. Try another spelling or import GeoNames city data.',
    ],
];
