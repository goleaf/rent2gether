<?php

namespace App\Models;

use Database\Factories\HostInspectionTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostInspectionTask extends Model
{
    /** @use HasFactory<HostInspectionTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'booking_check_out_id',
        'status',
        'scheduled_date',
        'scheduled_time',
        'reason',
        'checklist_json',
        'result_json',
        'note',
        'completed_at',
    ];

    protected $attributes = [
        'status' => 'planned',
    ];

    /**
     * Defines how Laravel converts stored Host Inspection Task attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'checklist_json' => 'array',
            'result_json' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Links this Host Inspection Task to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Host Inspection Task to the Property record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->host();
    }

    /**
     * Links this Host Inspection Task to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Host Inspection Task to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Host Inspection Task to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Host Inspection Task to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Host Inspection Task to the Booking Check Out record used by its check out relation.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Reuses the checkout relation as the canonical booking checkout link for this Host Inspection Task.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->checkOut();
    }
}
