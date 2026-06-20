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

    /**
     * Defines how Laravel converts stored City attributes into PHP values.
     */
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

    /**
     * Registers lifecycle hooks that keep City records consistent.
     */
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

    /**
     * Links this City to the Country record used by its country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Links this City to the Region record used by its region relation.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Lists related Property records for this City.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Lists related City Translation records for this City.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CityTranslation::class);
    }

    /**
     * Adds the active query filter for reusable City lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('is_active', true);
    }

    /**
     * Adds the visible query filter for reusable City lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Adds the translated query filter for reusable City lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        $locales = CountryTranslation::localeCandidates($locale);

        return $query->with([
            'translations' => fn (HasMany $query): HasMany => $query
                ->select(['id', 'city_id', 'locale', 'name'])
                ->whereIn('locale', $locales),
        ]);
    }

    /**
     * Adds the in country query filter for reusable City lookups.
     */
    public function scopeInCountry(Builder $query, int $countryId): Builder
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Adds the name prefix query filter for reusable City lookups.
     */
    public function scopeNamePrefix(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', GeoNameNormalizer::normalize($value).'%');
    }

    /**
     * Adds the name contains query filter for reusable City lookups.
     */
    public function scopeNameContains(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', '%'.GeoNameNormalizer::normalize($value).'%');
    }

    /**
     * Adds the name contains in locale query filter for reusable City lookups.
     */
    public function scopeNameContainsInLocale(Builder $query, string $value, ?string $locale = null): Builder
    {
        $normalized = GeoNameNormalizer::normalize($value);
        $locale = CountryTranslation::normalizeLocale($locale ?: app()->getLocale());

        return $query->where(fn (Builder $query): Builder => $query
            ->where('name_normalized', 'like', '%'.$normalized.'%')
            ->orWhereHas(
                'translations',
                fn (Builder $query): Builder => $query
                    ->where('locale', $locale)
                    ->where('name_normalized', 'like', '%'.$normalized.'%'),
            ));
    }

    /**
     * Returns the localized name text for this City.
     */
    public function localizedName(?string $locale = null): string
    {
        $locale = CountryTranslation::normalizeLocale($locale ?: app()->getLocale());

        if ($locale !== '') {
            if ($this->relationLoaded('translations')) {
                $translation = $this->translations
                    ->firstWhere('locale', $locale)
                    ?: $this->translations->firstWhere('locale', CountryTranslation::fallbackLocale());

                if ($translation) {
                    return $translation->name;
                }
            } else {
                foreach (CountryTranslation::localeCandidates($locale) as $candidateLocale) {
                    $translation = $this->translations()
                        ->where('locale', $candidateLocale)
                        ->first();

                    if ($translation) {
                        return $translation->name;
                    }
                }
            }
        }

        return $this->name;
    }
}
