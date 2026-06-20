<?php

namespace App\Models;

use App\Support\Geo\GeoNameNormalizer;
use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'geoname_id',
        'country_id',
        'region_id',
        'name',
        'ascii_name',
        'alternate_names',
        'name_normalized',
        'latitude',
        'longitude',
        'population',
        'timezone',
        'feature_class',
        'feature_code',
        'status',
        'source',
        'source_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'geoname_id' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'population' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (City $city): void {
            $city->ascii_name = $city->ascii_name ?: $city->name;
            $city->geoname_id = $city->geoname_id ?: (is_numeric($city->source_id) ? (int) $city->source_id : null);
            $city->source_id = $city->source_id ?: ($city->geoname_id ? (string) $city->geoname_id : null);
            $city->status = $city->status ?: (($city->is_active ?? true) ? self::STATUS_ACTIVE : self::STATUS_INACTIVE);
            $city->is_active = $city->status === self::STATUS_ACTIVE;
            $city->name_normalized = GeoNameNormalizer::normalize($city->ascii_name ?: $city->name);
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('is_active', true);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->active();
    }

    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query;
    }

    public function scopeInCountry(Builder $query, int $countryId): Builder
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeNamePrefix(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', GeoNameNormalizer::normalize($value).'%');
    }

    public function scopeNameContains(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', '%'.GeoNameNormalizer::normalize($value).'%');
    }
}
