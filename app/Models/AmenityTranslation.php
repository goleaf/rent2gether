<?php

namespace App\Models;

use App\Services\Catalog\AmenityRuleLookupService;
use Database\Factories\AmenityTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmenityTranslation extends Model
{
    /** @use HasFactory<AmenityTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'amenity_id',
        'locale',
        'name',
        'name_normalized',
        'description',
    ];

    /**
     * Registers lifecycle hooks that keep Amenity Translation records consistent.
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
     * Links this Amenity Translation to the Amenity record used by its amenity relation.
     */
    public function amenity(): BelongsTo
    {
        return $this->belongsTo(Amenity::class);
    }
}
