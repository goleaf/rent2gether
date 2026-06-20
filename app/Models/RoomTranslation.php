<?php

namespace App\Models;

use Database\Factories\RoomTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTranslation extends Model
{
    /** @use HasFactory<RoomTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'locale',
        'title',
        'short_description',
        'full_description',
        'summary',
        'description',
        'notes',
        'sleeping_arrangement',
        'privacy_notes',
        'room_description',
        'room_rules_text',
        'room_pros',
        'room_cons',
        'who_lives_nearby_text',
        'quiet_hours_text',
        'storage_instructions',
        'work_study_instructions',
        'food_rules_text',
        'conflict_instructions',
        'special_notes',
        'shared_space_instructions',
    ];

    /**
     * Links this Room Translation to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
