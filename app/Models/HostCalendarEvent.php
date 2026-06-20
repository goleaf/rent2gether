<?php

namespace App\Models;

use Database\Factories\HostCalendarEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCalendarEvent extends Model
{
    /** @use HasFactory<HostCalendarEventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'cleaning_task_id',
        'event_type',
        'event_status',
        'event_date',
        'starts_at',
        'ends_at',
        'title_key',
        'title_params_json',
        'description_key',
        'description_params_json',
        'guest_user_id',
        'guest_display_name',
        'check_in_date',
        'check_out_date',
        'nights_count',
        'payment_status',
        'check_in_status',
        'place_status',
        'needs_cleaning',
        'needs_inspection',
        'needs_repair',
        'price_amount',
        'currency',
        'payout_status',
        'payout_amount',
        'priority',
        'source',
        'host_note',
        'is_private',
    ];

    protected $attributes = [
        'priority' => 0,
        'is_private' => true,
        'needs_cleaning' => false,
        'needs_inspection' => false,
        'needs_repair' => false,
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date:Y-m-d',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'title_params_json' => 'array',
            'description_params_json' => 'array',
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'nights_count' => 'integer',
            'needs_cleaning' => 'boolean',
            'needs_inspection' => 'boolean',
            'needs_repair' => 'boolean',
            'price_amount' => 'decimal:2',
            'payout_amount' => 'decimal:2',
            'priority' => 'integer',
            'is_private' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(HostCleaningTask::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
