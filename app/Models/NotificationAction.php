<?php

namespace App\Models;

use Database\Factories\NotificationActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAction extends Model
{
    /** @use HasFactory<NotificationActionFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'user_id',
        'action_type',
        'status',
        'source_type',
        'source_id',
        'performed_at',
        'result_message_key',
        'result_context_json',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'result_context_json' => 'array',
        ];
    }

    /**
     * Links this action to its notification.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Links this action to the user who can perform it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
