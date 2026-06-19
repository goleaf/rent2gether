<?php

namespace App\Models;

use Database\Factories\GuestPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestPreference extends Model
{
    /** @use HasFactory<GuestPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_budget_min',
        'preferred_budget_max',
        'preferred_currency',
        'preferred_room_type',
        'preferred_sleeping_place_type',
        'wants_wifi',
        'wants_kitchen',
        'wants_washing_machine',
        'wants_locker',
        'wants_lower_bunk',
        'avoids_mixed_room',
        'avoids_smoking',
        'avoids_pets',
        'needs_late_check_in',
        'needs_workspace',
        'needs_quiet_hours',
        'accessibility_needs_json',
    ];

    protected function casts(): array
    {
        return [
            'preferred_budget_min' => 'decimal:2',
            'preferred_budget_max' => 'decimal:2',
            'wants_wifi' => 'boolean',
            'wants_kitchen' => 'boolean',
            'wants_washing_machine' => 'boolean',
            'wants_locker' => 'boolean',
            'wants_lower_bunk' => 'boolean',
            'avoids_mixed_room' => 'boolean',
            'avoids_smoking' => 'boolean',
            'avoids_pets' => 'boolean',
            'needs_late_check_in' => 'boolean',
            'needs_workspace' => 'boolean',
            'needs_quiet_hours' => 'boolean',
            'accessibility_needs_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
