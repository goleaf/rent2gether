<?php

namespace App\Models;

use App\Support\Geo\GeoNameNormalizer;
use Database\Factories\CityTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityTranslation extends Model
{
    /** @use HasFactory<CityTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'city_id',
        'locale',
        'name',
        'name_normalized',
        'source',
        'source_id',
        'is_preferred',
        'is_short',
        'is_colloquial',
        'is_historic',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'is_preferred' => 'boolean',
            'is_short' => 'boolean',
            'is_colloquial' => 'boolean',
            'is_historic' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CityTranslation $translation): void {
            $translation->locale = CountryTranslation::normalizeLocale($translation->locale);
            $translation->name_normalized = GeoNameNormalizer::normalize($translation->name);
        });
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
