<?php

namespace App\Models;

use Database\Factories\UserSavedPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSavedPreference extends Model
{
    /** @use HasFactory<UserSavedPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_currency',
        'preferred_locale',
        'preferred_timezone',
        'distance_unit',
        'price_display_mode',
        'date_format',
        'time_format',
        'mobile_compact_mode',
        'show_total_price_with_deposit',
        'show_total_price_without_deposit',
    ];

    protected function casts(): array
    {
        return [
            'mobile_compact_mode' => 'boolean',
            'show_total_price_with_deposit' => 'boolean',
            'show_total_price_without_deposit' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
