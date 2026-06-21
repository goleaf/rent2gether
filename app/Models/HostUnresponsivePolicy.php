<?php

namespace App\Models;

use Database\Factories\HostUnresponsivePolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostUnresponsivePolicy extends Model
{
    /** @use HasFactory<HostUnresponsivePolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'property_id',
        'pre_check_in_response_minutes',
        'check_in_response_minutes',
        'guest_waiting_outside_response_minutes',
        'night_entry_response_minutes',
        'urgent_response_minutes',
        'notify_representative_if_available',
        'auto_show_instructions_if_allowed',
        'auto_block_no_show_while_active',
        'allow_guest_cancellation_after_deadline',
        'allow_guest_relocation_after_deadline',
        'guest_friendly_refund_if_confirmed',
        'active',
    ];

    protected $attributes = [
        'pre_check_in_response_minutes' => 240,
        'check_in_response_minutes' => 60,
        'guest_waiting_outside_response_minutes' => 20,
        'night_entry_response_minutes' => 15,
        'urgent_response_minutes' => 10,
        'notify_representative_if_available' => true,
        'auto_show_instructions_if_allowed' => true,
        'auto_block_no_show_while_active' => true,
        'allow_guest_cancellation_after_deadline' => true,
        'allow_guest_relocation_after_deadline' => true,
        'guest_friendly_refund_if_confirmed' => true,
        'active' => true,
    ];

    /**
     * Defines how stored host-unresponsive policy values are converted for PHP use.
     */
    protected function casts(): array
    {
        return [
            'pre_check_in_response_minutes' => 'integer',
            'check_in_response_minutes' => 'integer',
            'guest_waiting_outside_response_minutes' => 'integer',
            'night_entry_response_minutes' => 'integer',
            'urgent_response_minutes' => 'integer',
            'notify_representative_if_available' => 'boolean',
            'auto_show_instructions_if_allowed' => 'boolean',
            'auto_block_no_show_while_active' => 'boolean',
            'allow_guest_cancellation_after_deadline' => 'boolean',
            'allow_guest_relocation_after_deadline' => 'boolean',
            'guest_friendly_refund_if_confirmed' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Links this policy to the sleeping place that can override property defaults.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this policy to the property fallback when no sleeping-place policy exists.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Lists immutable booking snapshots created from this policy's place or property values.
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(HostUnresponsivePolicySnapshot::class, 'sleeping_place_id', 'sleeping_place_id');
    }
}
