<?php

namespace App\Models;

use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'recipient_user_id',
        'channel',
        'status',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'read_at',
        'provider',
        'provider_message_id',
        'provider_response_json',
        'failure_reason',
        'attempt_count',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_response_json' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'read_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    /**
     * Links this delivery to its notification.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Links this delivery to the recipient user.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Lists provider attempts for this delivery.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(NotificationDeliveryAttempt::class);
    }
}
