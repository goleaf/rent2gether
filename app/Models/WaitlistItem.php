<?php

namespace App\Models;

use Database\Factories\WaitlistItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistItem extends Model
{
    /** @use HasFactory<WaitlistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sleeping_place_id',
        'desired_check_in',
        'desired_check_out',
        'max_price',
        'ready_to_book',
        'auto_request',
        'notified',
        'notified_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'desired_check_in' => 'date',
            'desired_check_out' => 'date',
            'max_price' => 'decimal:2',
            'ready_to_book' => 'boolean',
            'auto_request' => 'boolean',
            'notified' => 'boolean',
            'notified_at' => 'datetime',
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

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
