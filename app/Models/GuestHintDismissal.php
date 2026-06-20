<?php

namespace App\Models;

use Database\Factories\GuestHintDismissalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHintDismissal extends Model
{
    /** @use HasFactory<GuestHintDismissalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sleeping_place_id',
        'hint_key',
        'context',
        'dismissed_at',
        'expires_at',
    ];

    /**
     * Defines how Laravel converts stored Guest Hint Dismissal attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Links this Guest Hint Dismissal to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Guest Hint Dismissal to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Adds the active query filter for reusable Guest Hint Dismissal lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
