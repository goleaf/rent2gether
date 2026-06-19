<?php

namespace App\Models;

use Database\Factories\SleepingPlaceTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceTranslation extends Model
{
    /** @use HasFactory<SleepingPlaceTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'locale',
        'title',
        'summary',
        'description',
        'privacy_notes',
        'accessibility_notes',
    ];

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
