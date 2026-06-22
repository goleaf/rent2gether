<?php

namespace App\Models;

use Database\Factories\UserSettingFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JsonException;

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
     * Links this User Setting to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normalizes notification preference JSON into an array when read or written.
     */
    protected function notificationPreferencesJson(): Attribute
    {
        return $this->arrayPreferenceAttribute();
    }

    /**
     * Normalizes privacy preference JSON into an array when read or written.
     */
    protected function privacyPreferencesJson(): Attribute
    {
        return $this->arrayPreferenceAttribute();
    }

    /**
     * Builds an Eloquent attribute handler for JSON preference payloads.
     */
    private function arrayPreferenceAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): ?array => self::decodePreferenceArray($value),
            set: fn (mixed $value): ?string => self::encodePreferenceArray($value),
        );
    }

    /**
     * Decodes normal JSON arrays and legacy double-encoded JSON strings.
     *
     * @return array<string, mixed>|null
     */
    private static function decodePreferenceArray(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = $value;

        for ($attempt = 0; $attempt < 2 && is_string($decoded); $attempt++) {
            try {
                $decoded = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return null;
            }
        }

        return is_array($decoded) ? $decoded : null;
    }

    private static function encodePreferenceArray(mixed $value): ?string
    {
        $decoded = self::decodePreferenceArray($value);

        if ($decoded === null) {
            return null;
        }

        return json_encode($decoded, JSON_THROW_ON_ERROR);
    }
}
