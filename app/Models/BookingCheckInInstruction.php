<?php

namespace App\Models;

use Database\Factories\BookingCheckInInstructionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInInstruction extends Model
{
    /** @use HasFactory<BookingCheckInInstructionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'instruction_title',
        'public_instruction_text',
        'address_instruction_text',
        'building_entry_instruction',
        'room_finding_instruction',
        'sleeping_place_instruction',
        'key_pickup_instruction',
        'key_return_instruction',
        'night_entry_instruction',
        'emergency_instruction',
        'exact_address_snapshot',
        'room_identifier_snapshot',
        'sleeping_place_identifier_snapshot',
        'door_code_encrypted',
        'intercom_code_encrypted',
        'key_safe_code_encrypted',
        'visible_from',
        'visible_until',
    ];

    /**
     * Defines casts for stored instruction snapshot and encrypted access data.
     */
    protected function casts(): array
    {
        return [
            'door_code_encrypted' => 'encrypted',
            'intercom_code_encrypted' => 'encrypted',
            'key_safe_code_encrypted' => 'encrypted',
            'visible_from' => 'datetime',
            'visible_until' => 'datetime',
        ];
    }

    /**
     * Links this instruction snapshot to its check-in process.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this instruction snapshot to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this instruction snapshot to its property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this instruction snapshot to its room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this instruction snapshot to its sleeping-place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
