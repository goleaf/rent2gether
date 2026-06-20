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

    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
    }

    protected function casts(): array
    {
        return [
            'requires_confirmation' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(RuleTranslation::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_rule')->withTimestamps();
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_rule')->withTimestamps();
    }

    public function sleepingPlaces(): BelongsToMany
    {
        return $this->belongsToMany(SleepingPlace::class, 'sleeping_place_rule')->withTimestamps();
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
