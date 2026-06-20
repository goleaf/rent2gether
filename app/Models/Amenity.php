<?php

namespace App\Models;

use App\Services\Catalog\AmenityRuleLookupService;
use Database\Factories\AmenityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Amenity extends Model
{
    /** @use HasFactory<AmenityFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_normalized',
        'category',
        'icon',
        'status',
    ];

    /**
     * Registers lifecycle hooks that keep Amenity records consistent.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearAmenityCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearAmenityCache();
        });
    }

    /**
     * Lists related Amenity Translation records for this Amenity.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(AmenityTranslation::class);
    }

    /**
     * Connects this Amenity to related Property records through a pivot relation.
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_amenity')->withTimestamps();
    }

    /**
     * Connects this Amenity to related Room records through a pivot relation.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_amenity')->withTimestamps();
    }

    /**
     * Connects this Amenity to related Sleeping Place records through a pivot relation.
     */
    public function sleepingPlaces(): BelongsToMany
    {
        return $this->belongsToMany(SleepingPlace::class, 'sleeping_place_amenity')->withTimestamps();
    }

    /**
     * Adds the active query filter for reusable Amenity lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Adds the visible query filter for reusable Amenity lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Adds the translated query filter for reusable Amenity lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }
}
