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
        'avatar_path',
        'about',
        'languages_json',
        'response_time_minutes',
        'response_rate',
        'lives_in_property',
        'lives_nearby',
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
            'lives_in_property' => 'boolean',
            'lives_nearby' => 'boolean',
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
