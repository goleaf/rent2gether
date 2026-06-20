<?php

namespace App\Support\Geo;

use Illuminate\Support\Str;

class GeoNameNormalizer
{
    public static function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->squish()
            ->toString();

        return trim($normalized);
    }
}
