<?php

namespace App\Models;

use App\Support\Geo\GeoNameNormalizer;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'iso2',
        'code',
        'iso3',
        'name_en',
        'name_ru',
        'name_native',
        'name',
        'name_normalized',
        'currency_code',
        'phone_code',
        'timezone_default',
        'status',
        'source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Country $country): void {
            $country->iso2 = strtoupper((string) ($country->iso2 ?: $country->code));
            $country->code = strtoupper((string) ($country->code ?: $country->iso2));
            $country->iso3 = $country->iso3 === null ? null : strtoupper($country->iso3);
            $country->name_en = $country->name_en ?: $country->name;
            $country->name = $country->name ?: $country->name_en;
            $country->status = $country->status ?: (($country->is_active ?? true) ? self::STATUS_ACTIVE : self::STATUS_INACTIVE);
            $country->is_active = $country->status === self::STATUS_ACTIVE;
            $country->name_normalized = GeoNameNormalizer::normalize($country->name_en ?: $country->name);
        });
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
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

    public function scopeNamePrefix(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', GeoNameNormalizer::normalize($value).'%');
    }

    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === 'ru' && $this->name_ru) {
            return $this->name_ru;
        }

        return $this->name_en ?: $this->name;
    }
}
