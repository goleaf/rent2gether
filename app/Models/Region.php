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

    protected static function booted(): void
    {
        static::saving(function (Region $region): void {
            $region->name_normalized = GeoNameNormalizer::normalize($region->name);
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function scopeNamePrefix(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', GeoNameNormalizer::normalize($value).'%');
    }
}
