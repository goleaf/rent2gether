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

    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearAmenityCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearAmenityCache();
        });
    }

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(Amenity::class);
    }
}
