<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\HostProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostProfile extends Model
{
    /** @use HasFactory<HostProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'host_display_name',
        'host_type',
        'avatar_path',
        'about',
        'about_host',
        'languages_json',
        'response_time_minutes',
        'response_rate',
        'acceptance_rate',
        'response_style',
        'lives_in_property',
        'lives_nearby',
        'can_help_with_check_in',
        'emergency_contact_available',
        'hosting_experience',
        'default_check_in_time',
        'default_check_out_time',
        'default_cancellation_policy',
        'default_deposit_setting',
        'default_house_rules',
        'successful_check_ins_count',
        'host_cancellations_count',
        'complaints_count',
        'verified_host',
        'hosting_since',
        'default_currency',
        'default_language',
        'public_phone_visible',
        'public_email_visible',
        'emergency_contact_name',
        'emergency_contact_phone',
        'representative_name',
        'representative_contact',
        'representative_visible_to_guest',
        'rating_average',
        'reviews_count',
        'cancellations_count',
        'verified_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'languages_json' => 'array',
            'response_rate' => 'decimal:2',
            'acceptance_rate' => 'decimal:2',
            'lives_in_property' => 'boolean',
            'lives_nearby' => 'boolean',
            'can_help_with_check_in' => 'boolean',
            'emergency_contact_available' => 'boolean',
            'verified_host' => 'boolean',
            'hosting_since' => 'date',
            'public_phone_visible' => 'boolean',
            'public_email_visible' => 'boolean',
            'representative_visible_to_guest' => 'boolean',
            'rating_average' => 'decimal:2',
            'verified_at' => 'datetime',
            'status' => UserStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
