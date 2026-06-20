<?php

namespace App\Models;

use Database\Factories\HostRepresentativeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostRepresentative extends Model
{
    /** @use HasFactory<HostRepresentativeFactory> */
    use HasFactory;

    protected $fillable = [
        'host_user_id',
        'representative_user_id',
        'name',
        'phone',
        'email',
        'role_description',
        'can_help_with_check_in',
        'can_help_with_keys',
        'can_help_with_cleaning_coordination',
        'can_be_contacted_by_guest',
        'visible_after_booking_only',
        'active',
    ];

    /**
     * Defines how Laravel converts stored Host Representative attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'can_help_with_check_in' => 'boolean',
            'can_help_with_keys' => 'boolean',
            'can_help_with_cleaning_coordination' => 'boolean',
            'can_be_contacted_by_guest' => 'boolean',
            'visible_after_booking_only' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Links this Host Representative to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Host Representative to the User record used by its representative user relation.
     */
    public function representativeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_user_id');
    }
}
