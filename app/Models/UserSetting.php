<?php

namespace App\Models;

use Database\Factories\UserSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    /** @use HasFactory<UserSettingFactory> */
    use HasFactory;

    public const MODE_GUEST = 'guest';

    public const MODE_HOST = 'host';

    public const ROLE_GUEST = 'guest';

    public const ROLE_HOST = 'host';

    public const ROLE_BOTH = 'both';

    protected $fillable = [
        'user_id',
        'locale',
        'currency',
        'active_mode',
        'account_role',
        'notification_preferences_json',
        'privacy_preferences_json',
    ];

    protected $attributes = [
        'locale' => 'en',
        'currency' => 'EUR',
        'active_mode' => self::MODE_GUEST,
        'account_role' => self::ROLE_GUEST,
    ];

    /**
     * Defines how Laravel converts stored User Setting attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'notification_preferences_json' => 'array',
            'privacy_preferences_json' => 'array',
        ];
    }

    /**
     * Links this User Setting to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
