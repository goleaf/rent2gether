<?php

namespace App\Models;

use Database\Factories\UserPrivacySettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrivacySetting extends Model
{
    /** @use HasFactory<UserPrivacySettingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'show_real_name',
        'show_avatar',
        'show_age_range',
        'show_gender',
        'show_city',
        'show_languages',
        'show_rating',
        'show_completed_stays_count',
        'show_reviews_count',
        'show_phone_after_booking',
        'show_email_after_booking',
        'show_identity_verified_badge',
        'allow_host_to_see_guest_profile',
        'allow_guest_to_see_host_contact_after_booking',
    ];

    protected function casts(): array
    {
        return [
            'show_real_name' => 'boolean',
            'show_avatar' => 'boolean',
            'show_age_range' => 'boolean',
            'show_gender' => 'boolean',
            'show_city' => 'boolean',
            'show_languages' => 'boolean',
            'show_rating' => 'boolean',
            'show_completed_stays_count' => 'boolean',
            'show_reviews_count' => 'boolean',
            'show_phone_after_booking' => 'boolean',
            'show_email_after_booking' => 'boolean',
            'show_identity_verified_badge' => 'boolean',
            'allow_host_to_see_guest_profile' => 'boolean',
            'allow_guest_to_see_host_contact_after_booking' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
