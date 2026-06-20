<?php

namespace App\Models;

use Database\Factories\ListingCreationDraftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCreationDraft extends Model
{
    /** @use HasFactory<ListingCreationDraftFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'draft_type',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'current_step',
        'draft_data_json',
        'completed_steps_json',
        'last_saved_at',
    ];

    /**
     * Defines how Laravel converts stored Listing Creation Draft attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'draft_data_json' => 'array',
            'completed_steps_json' => 'array',
            'last_saved_at' => 'datetime',
        ];
    }

    /**
     * Links this Listing Creation Draft to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Listing Creation Draft to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Listing Creation Draft to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Listing Creation Draft to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
