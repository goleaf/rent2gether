<?php

namespace App\Models;

use Database\Factories\RoomConditionDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomConditionDetail extends Model
{
    /** @use HasFactory<RoomConditionDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'condition_state',
        'repair_state',
        'cleanliness_level',
        'floor_condition',
        'walls_condition',
        'ceiling_condition',
        'window_condition',
        'door_condition',
        'lock_condition',
        'furniture_condition',
        'wardrobe_condition',
        'desk_condition',
        'chairs_condition',
        'balcony_condition',
        'has_dust',
        'has_bad_smell',
        'has_damp_marks',
        'has_mold',
        'has_insects',
        'has_damage',
        'needs_repair',
        'recently_renovated',
        'needs_refresh',
        'last_cleaned_at',
        'last_checked_at',
        'last_repaired_at',
        'host_condition_note',
    ];

    protected function casts(): array
    {
        return [
            'has_dust' => 'boolean',
            'has_bad_smell' => 'boolean',
            'has_damp_marks' => 'boolean',
            'has_mold' => 'boolean',
            'has_insects' => 'boolean',
            'has_damage' => 'boolean',
            'needs_repair' => 'boolean',
            'recently_renovated' => 'boolean',
            'needs_refresh' => 'boolean',
            'last_cleaned_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'last_repaired_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
