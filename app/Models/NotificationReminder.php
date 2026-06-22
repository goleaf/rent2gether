<?php

namespace App\Models;

use Database\Factories\NotificationReminderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationReminder extends Model
{
    /** @use HasFactory<NotificationReminderFactory> */
    use HasFactory;

    protected $fillable = [
        'reminder_number',
        'user_id',
        'recipient_type',
        'reminder_type',
        'status',
        'priority',
        'source_type',
        'source_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'notification_template_id',
        'scheduled_for',
        'due_at',
        'processed_at',
        'cancelled_at',
        'expired_at',
        'translation_params_json',
        'action_type',
        'action_url',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'due_at' => 'datetime',
            'processed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
            'translation_params_json' => 'array',
        ];
    }

    /**
     * Links this reminder to its recipient user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this reminder to its template when present.
     */
    public function notificationTemplate(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class);
    }

    /**
     * Links this reminder to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this reminder to the property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this reminder to the room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this reminder to the sleeping-place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
