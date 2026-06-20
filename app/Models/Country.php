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
        'geoname_id',
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

    /**
     * Defines how Laravel converts stored Country attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'geoname_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Registers lifecycle hooks that keep Country records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (Country $country): void {
            $country->iso2 = strtoupper((string) ($country->iso2 ?: $country->code));
            $country->code = strtoupper((string) ($country->code ?: $country->iso2));
            $country->iso3 = $country->iso3 === null ? null : strtoupper($country->iso3);
            $country->name = $country->name ?: ($country->name_en ?: $country->name_native ?: $country->iso2);
            $country->name_en = $country->name_en ?: $country->name;
            $country->status = $country->status ?: (($country->is_active ?? true) ? self::STATUS_ACTIVE : self::STATUS_INACTIVE);
            $country->is_active = $country->status === self::STATUS_ACTIVE;
            $country->name_normalized = GeoNameNormalizer::normalize($country->name);
        });
    }

    /**
     * Lists related Region records for this Country.
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    /**
     * Lists related City records for this Country.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Lists related Country Translation records for this Country.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    /**
     * Adds the active query filter for reusable Country lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('is_active', true);
    }

    /**
     * Adds the visible query filter for reusable Country lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Adds the translated query filter for reusable Country lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        $locales = CountryTranslation::localeCandidates($locale);

        return $query->with([
            'translations' => fn (HasMany $query): HasMany => $query
                ->select(['id', 'country_id', 'locale', 'name'])
                ->whereIn('locale', $locales),
        ]);
    }

    /**
     * Adds the name prefix query filter for reusable Country lookups.
     */
    public function scopeNamePrefix(Builder $query, string $value): Builder
    {
        return $query->where('name_normalized', 'like', GeoNameNormalizer::normalize($value).'%');
    }

    /**
     * Returns the localized name text for this Country.
     */
    public function localizedName(?string $locale = null): string
    {
        $locale = CountryTranslation::normalizeLocale($locale ?: app()->getLocale());

        $translation = $this->translationForLocale($locale);

        if ($translation) {
            return $translation->name;
        }

        return $this->name;
    }

    /**
     * Returns the Country translation for the requested locale when it exists.
     */
    private function translationForLocale(string $locale): ?CountryTranslation
    {
        if ($locale === '') {
            return null;
        }

        if ($this->relationLoaded('translations')) {
            return $this->translations
                ->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere('locale', CountryTranslation::fallbackLocale());
        }

        foreach (CountryTranslation::localeCandidates($locale) as $candidateLocale) {
            $translation = $this->translations()
                ->where('locale', $candidateLocale)
                ->first();

            if ($translation) {
                return $translation;
            }
        }

        return null;
    }
}
