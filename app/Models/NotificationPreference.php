<?php

namespace App\Models;

use Database\Factories\NotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    /** @use HasFactory<NotificationPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'in_app_enabled',
        'email_enabled',
        'sms_future_enabled',
        'push_future_enabled',
        'urgent_always_in_app',
        'critical_ignore_quiet_hours',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'timezone',
        'language_locale',
        'digest_type',
        'digest_time',
    ];

    protected function casts(): array
    {
        return [
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_future_enabled' => 'boolean',
            'push_future_enabled' => 'boolean',
            'urgent_always_in_app' => 'boolean',
            'critical_ignore_quiet_hours' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
        ];
    }

    /**
     * Links this preference set to its user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
