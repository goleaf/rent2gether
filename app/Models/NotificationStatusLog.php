<?php

namespace App\Models;

use Database\Factories\NotificationStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationStatusLog extends Model
{
    /** @use HasFactory<NotificationStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'notification_delivery_id',
        'notification_reminder_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its notification.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Links this status log to its delivery when present.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(NotificationDelivery::class, 'notification_delivery_id');
    }

    /**
     * Links this status log to its reminder when present.
     */
    public function reminder(): BelongsTo
    {
        return $this->belongsTo(NotificationReminder::class, 'notification_reminder_id');
    }

    /**
     * Links this status log to the user that caused it when present.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
