<?php

namespace App\Models;

use Database\Factories\NotificationCategoryPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationCategoryPreference extends Model
{
    /** @use HasFactory<NotificationCategoryPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_category',
        'in_app_enabled',
        'email_enabled',
        'sms_future_enabled',
        'push_future_enabled',
        'digest_only',
        'urgent_allowed',
        'critical_allowed',
    ];

    protected function casts(): array
    {
        return [
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_future_enabled' => 'boolean',
            'push_future_enabled' => 'boolean',
            'digest_only' => 'boolean',
            'urgent_allowed' => 'boolean',
            'critical_allowed' => 'boolean',
        ];
    }

    /**
     * Links this category preference to its user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
