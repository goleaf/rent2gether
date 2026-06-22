<?php

namespace App\Models;

use Database\Factories\CleaningPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningPolicy extends Model
{
    /** @use HasFactory<CleaningPolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'room_id',
        'sleeping_place_id',
        'cleaning_required_after_checkout',
        'cleaning_required_before_checkin',
        'inspection_required_after_cleaning',
        'default_cleaning_duration_minutes',
        'default_inspection_duration_minutes',
        'same_day_turnover_min_gap_minutes',
        'require_before_photos',
        'require_after_photos',
        'require_checklist_completion',
        'require_host_confirmation',
        'auto_create_after_checkout',
        'auto_create_before_checkin',
        'auto_create_after_complaint',
        'auto_create_after_repair',
        'active',
    ];

    protected $attributes = [
        'cleaning_required_after_checkout' => true,
        'cleaning_required_before_checkin' => false,
        'inspection_required_after_cleaning' => false,
        'default_cleaning_duration_minutes' => 120,
        'default_inspection_duration_minutes' => 30,
        'same_day_turnover_min_gap_minutes' => 180,
        'require_before_photos' => false,
        'require_after_photos' => true,
        'require_checklist_completion' => true,
        'require_host_confirmation' => false,
        'auto_create_after_checkout' => true,
        'auto_create_before_checkin' => false,
        'auto_create_after_complaint' => true,
        'auto_create_after_repair' => true,
        'active' => true,
    ];

    /**
     * Defines how stored cleaning policy attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'cleaning_required_after_checkout' => 'boolean',
            'cleaning_required_before_checkin' => 'boolean',
            'inspection_required_after_cleaning' => 'boolean',
            'default_cleaning_duration_minutes' => 'integer',
            'default_inspection_duration_minutes' => 'integer',
            'same_day_turnover_min_gap_minutes' => 'integer',
            'require_before_photos' => 'boolean',
            'require_after_photos' => 'boolean',
            'require_checklist_completion' => 'boolean',
            'require_host_confirmation' => 'boolean',
            'auto_create_after_checkout' => 'boolean',
            'auto_create_before_checkin' => 'boolean',
            'auto_create_after_complaint' => 'boolean',
            'auto_create_after_repair' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Links the policy to the property it configures.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links the policy to the room it configures.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links the policy to the sleeping place it configures.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
