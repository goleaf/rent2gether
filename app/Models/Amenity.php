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

    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearAmenityCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearAmenityCache();
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AmenityTranslation::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_amenity')->withTimestamps();
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_amenity')->withTimestamps();
    }

    public function sleepingPlaces(): BelongsToMany
    {
        return $this->belongsToMany(SleepingPlace::class, 'sleeping_place_amenity')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->active();
    }

    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }
}
