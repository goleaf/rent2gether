<?php

namespace App\Models;

use Database\Factories\HostUnresponsivePolicySnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsivePolicySnapshot extends Model
{
    /** @use HasFactory<HostUnresponsivePolicySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
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
        'policy_snapshot_json',
    ];

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
            'policy_snapshot_json' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
