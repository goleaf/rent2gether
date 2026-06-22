<?php

namespace App\Models;

use Database\Factories\NotificationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationEvent extends Model
{
    /** @use HasFactory<NotificationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'event_number',
        'event_key',
        'event_type',
        'notification_category',
        'source_type',
        'source_id',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'booking_extension_id',
        'booking_relocation_id',
        'booking_cancellation_id',
        'booking_no_show_id',
        'host_unresponsive_case_id',
        'listing_mismatch_report_id',
        'complaint_case_id',
        'dispute_case_id',
        'booking_deposit_id',
        'maintenance_request_id',
        'inventory_issue_id',
        'cleaning_task_id',
        'inspection_task_id',
        'saved_search_id',
        'favorite_id',
        'waitlist_entry_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'created_by_user_id',
        'payload_json',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
        ];
    }

    /**
     * Links this event to the booking context when present.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this event to the property context when present.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this event to the room context when present.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this event to the sleeping-place context when present.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists notifications produced from this event.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
