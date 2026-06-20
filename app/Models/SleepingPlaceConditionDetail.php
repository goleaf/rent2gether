<?php

namespace App\Models;

use Database\Factories\SleepingPlaceConditionDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceConditionDetail extends Model
{
    /** @use HasFactory<SleepingPlaceConditionDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'condition_state',
        'frame_condition',
        'mattress_condition',
        'bedding_condition',
        'pillow_condition',
        'blanket_condition',
        'curtain_condition',
        'lamp_condition',
        'socket_condition',
        'locker_condition',
        'lock_condition',
        'has_damage',
        'has_stains',
        'has_smell',
        'squeaks',
        'needs_repair',
        'needs_mattress_replacement',
        'needs_bedding_replacement',
        'last_cleaned_at',
        'last_bedding_changed_at',
        'last_checked_at',
        'last_repaired_at',
        'host_condition_note',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Condition Detail attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'has_damage' => 'boolean',
            'has_stains' => 'boolean',
            'has_smell' => 'boolean',
            'squeaks' => 'boolean',
            'needs_repair' => 'boolean',
            'needs_mattress_replacement' => 'boolean',
            'needs_bedding_replacement' => 'boolean',
            'last_cleaned_at' => 'datetime',
            'last_bedding_changed_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'last_repaired_at' => 'datetime',
        ];
    }

    /**
     * Links this Sleeping Place Condition Detail to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
