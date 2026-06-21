<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCalendarBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCalendarBlock extends Model
{
    /** @use HasFactory<SleepingPlaceCalendarBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'room_id',
        'property_id',
        'booking_id',
        'source_type',
        'source_id',
        'block_type',
        'status',
        'starts_at',
        'ends_at',
        'check_in_date',
        'check_out_date',
        'reason_key',
        'visible_to_guest',
        'created_by_user_id',
        'released_at',
    ];

    protected $attributes = [
        'status' => 'active',
        'visible_to_guest' => false,
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Calendar Block attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'visible_to_guest' => 'boolean',
            'released_at' => 'datetime',
        ];
    }

    /**
     * Links this calendar block to its Sleeping Place.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this calendar block to the affected Room when the block came from a room-level issue.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this calendar block to the affected Property when the block came from a property-level issue.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this calendar block to the related Booking when a booking created the block.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this calendar block to the User who created it.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
