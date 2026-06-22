<?php

namespace App\Models;

use Database\Factories\NotificationDeliveryAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDeliveryAttempt extends Model
{
    /** @use HasFactory<NotificationDeliveryAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_delivery_id',
        'notification_id',
        'channel',
        'attempt_number',
        'status',
        'attempted_at',
        'provider',
        'provider_response_json',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'provider_response_json' => 'array',
            'attempted_at' => 'datetime',
        ];
    }

    /**
     * Links this attempt to its delivery record.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(NotificationDelivery::class, 'notification_delivery_id');
    }

    /**
     * Links this attempt to its notification.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
