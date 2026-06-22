<?php

namespace App\Models;

use Database\Factories\NotificationSystemEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSystemEvent extends Model
{
    /** @use HasFactory<NotificationSystemEventFactory> */
    use HasFactory;

    protected $fillable = [
        'event_key',
        'event_type',
        'notification_id',
        'notification_event_id',
        'notification_delivery_id',
        'notification_reminder_id',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this system event to its notification when present.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Links this system event to its source event when present.
     */
    public function notificationEvent(): BelongsTo
    {
        return $this->belongsTo(NotificationEvent::class);
    }

    /**
     * Links this system event to a delivery when present.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(NotificationDelivery::class, 'notification_delivery_id');
    }

    /**
     * Links this system event to a reminder when present.
     */
    public function reminder(): BelongsTo
    {
        return $this->belongsTo(NotificationReminder::class, 'notification_reminder_id');
    }

    /**
     * Links this system event to the user involved when present.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
