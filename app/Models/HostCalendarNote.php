<?php

namespace App\Models;

use Database\Factories\HostCalendarNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCalendarNote extends Model
{
    /** @use HasFactory<HostCalendarNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'note_date',
        'note_type',
        'note',
        'is_private',
    ];

    protected $attributes = [
        'note_type' => 'general',
        'is_private' => true,
    ];

    /**
     * Defines how Laravel converts stored Host Calendar Note attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'note_date' => 'date:Y-m-d',
            'is_private' => 'boolean',
        ];
    }

    /**
     * Links this Host Calendar Note to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Host Calendar Note to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Host Calendar Note to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Host Calendar Note to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Host Calendar Note to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
