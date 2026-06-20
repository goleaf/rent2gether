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

    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
