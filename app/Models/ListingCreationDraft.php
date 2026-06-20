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

    protected function casts(): array
    {
        return [
            'draft_data_json' => 'array',
            'completed_steps_json' => 'array',
            'last_saved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
