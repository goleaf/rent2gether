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
        'short_description',
        'full_description',
        'summary',
        'description',
        'special_conditions',
        'privacy_notes',
        'accessibility_notes',
        'main_pros',
        'important_cons',
        'special_notes',
        'what_is_included',
        'what_guest_should_bring',
        'storage_instructions',
        'safety_notes',
        'sleeping_place_title',
        'sleeping_place_description',
        'sleeping_place_pros',
        'sleeping_place_cons',
        'sleeping_place_special_notes',
        'what_is_included_for_place',
        'what_guest_should_bring_for_place',
    ];

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
