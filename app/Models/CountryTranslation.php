<?php

namespace App\Models;

use App\Support\Geo\GeoNameNormalizer;
use Database\Factories\CountryTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryTranslation extends Model
{
    /** @use HasFactory<CountryTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'country_id',
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

    /**
     * Defines how Laravel converts stored Country Translation attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'is_preferred' => 'boolean',
            'is_short' => 'boolean',
            'is_colloquial' => 'boolean',
            'is_historic' => 'boolean',
        ];
    }

    /**
     * Registers lifecycle hooks that keep Country Translation records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (CountryTranslation $translation): void {
            $translation->locale = self::normalizeLocale($translation->locale);
            $translation->name_normalized = GeoNameNormalizer::normalize($translation->name);
        });
    }

    /**
     * Links this Country Translation to the Country record used by its country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Returns the normalize locale text for this Country Translation.
     */
    public static function normalizeLocale(?string $locale): string
    {
        $locale = str_replace('_', '-', trim((string) $locale));

        if ($locale === '') {
            return '';
        }

        $parts = explode('-', $locale);
        $parts[0] = mb_strtolower($parts[0]);

        foreach ($parts as $index => $part) {
            if ($index === 0) {
                continue;
            }

            $parts[$index] = mb_strlen($part) === 2
                ? mb_strtoupper($part)
                : mb_convert_case($part, MB_CASE_TITLE);
        }

        return implode('-', $parts);
    }

    /**
     * @return list<string>
     */
    public static function localeCandidates(?string $locale): array
    {
        return array_values(array_filter(array_unique([
            self::normalizeLocale($locale ?: app()->getLocale()),
            self::fallbackLocale(),
        ])));
    }

    /**
     * Returns the fallback locale text for this Country Translation.
     */
    public static function fallbackLocale(): string
    {
        return self::normalizeLocale((string) config(
            'localization.fallback_locale',
            config('app.fallback_locale'),
        ));
    }
}
