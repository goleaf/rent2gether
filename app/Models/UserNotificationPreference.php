<?php

namespace App\Models;

use Database\Factories\UserNotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    /** @use HasFactory<UserNotificationPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'channel',
        'enabled',
        'urgent_allowed',
        'quiet_hours_enabled',
        'quiet_hours_from',
        'quiet_hours_to',
    ];

    /**
     * Defines how Laravel converts stored User Notification Preference attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'urgent_allowed' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
        ];
    }

    /**
     * Links this User Notification Preference to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
