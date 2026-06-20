<?php

namespace App\Models;

use App\Support\Geo\GeoNameNormalizer;
use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    protected $fillable = [
        'country_id',
        'code',
        'name',
        'name_normalized',
        'source',
        'source_id',
    ];

    /**
     * Registers lifecycle hooks that keep Region records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (Region $region): void {
            $region->name_normalized = GeoNameNormalizer::normalize($region->name);
        });
    }

    /**
     * Links this Region to the Country record used by its country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Lists related City records for this Region.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Adds the name prefix query filter for reusable Region lookups.
     */
    public function scopeNamePrefix(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', GeoNameNormalizer::normalize($value).'%');
    }
}
