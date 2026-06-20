<?php

namespace App\Models;

use App\Services\Catalog\AmenityRuleLookupService;
use Database\Factories\RuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rule extends Model
{
    /** @use HasFactory<RuleFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_normalized',
        'category',
        'requires_confirmation',
        'status',
    ];

    /**
     * Registers lifecycle hooks that keep Rule records consistent.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
    }

    /**
     * Defines how Laravel converts stored Rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'requires_confirmation' => 'boolean',
        ];
    }

    /**
     * Lists related Rule Translation records for this Rule.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(RuleTranslation::class);
    }

    /**
     * Connects this Rule to related Property records through a pivot relation.
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_rule')->withTimestamps();
    }

    /**
     * Connects this Rule to related Room records through a pivot relation.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_rule')->withTimestamps();
    }

    /**
     * Connects this Rule to related Sleeping Place records through a pivot relation.
     */
    public function sleepingPlaces(): BelongsToMany
    {
        return $this->belongsToMany(SleepingPlace::class, 'sleeping_place_rule')->withTimestamps();
    }

    /**
     * Adds the active query filter for reusable Rule lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Adds the visible query filter for reusable Rule lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Adds the translated query filter for reusable Rule lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }
}
