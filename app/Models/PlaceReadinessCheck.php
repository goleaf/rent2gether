<?php

namespace App\Models;

use Database\Factories\PlaceReadinessCheckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceReadinessCheck extends Model
{
    /** @use HasFactory<PlaceReadinessCheckFactory> */
    use HasFactory;

    protected $fillable = [
        'readiness_number',
        'booking_id',
        'next_booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'host_user_id',
        'status',
        'check_reason',
        'target_check_in_at',
        'checkout_completed',
        'cleaning_completed',
        'inspection_completed',
        'repair_completed',
        'inventory_ready',
        'access_ready',
        'deposit_review_not_blocking',
        'complaint_not_blocking',
        'calendar_available',
        'same_day_turnover',
        'turnover_gap_minutes',
        'required_gap_minutes',
        'gap_is_enough',
        'blocking_reason_key',
        'blocking_reason_text',
        'ready_at',
        'closed_at',
    ];

    protected $attributes = [
        'status' => 'checking',
        'checkout_completed' => false,
        'cleaning_completed' => false,
        'inspection_completed' => false,
        'repair_completed' => false,
        'inventory_ready' => false,
        'access_ready' => false,
        'deposit_review_not_blocking' => true,
        'complaint_not_blocking' => true,
        'calendar_available' => false,
        'same_day_turnover' => false,
        'gap_is_enough' => true,
    ];

    /**
     * Defines how stored readiness check attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'target_check_in_at' => 'datetime',
            'checkout_completed' => 'boolean',
            'cleaning_completed' => 'boolean',
            'inspection_completed' => 'boolean',
            'repair_completed' => 'boolean',
            'inventory_ready' => 'boolean',
            'access_ready' => 'boolean',
            'deposit_review_not_blocking' => 'boolean',
            'complaint_not_blocking' => 'boolean',
            'calendar_available' => 'boolean',
            'same_day_turnover' => 'boolean',
            'turnover_gap_minutes' => 'integer',
            'required_gap_minutes' => 'integer',
            'gap_is_enough' => 'boolean',
            'ready_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this readiness check to the current or previous booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this readiness check to the next booking when known.
     */
    public function nextBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'next_booking_id');
    }

    /**
     * Links this readiness check to the host who owns the affected place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this readiness check to the affected property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this readiness check to the affected room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this readiness check to the sleeping place that must be prepared.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
