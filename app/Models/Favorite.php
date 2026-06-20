<?php

namespace App\Models;

use Database\Factories\FavoriteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    /** @use HasFactory<FavoriteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bed_id',
        'sleeping_place_id',
        'collection',
        'note',
        'priority',
        'price_at_save',
        'check_in',
        'check_out',
        'guests_count',
        'notify_available',
        'notify_price_drop',
    ];

    protected function casts(): array
    {
        return [
            'price_at_save' => 'decimal:2',
            'check_in' => 'date',
            'check_out' => 'date',
            'guests_count' => 'integer',
            'priority' => 'integer',
            'notify_available' => 'boolean',
            'notify_price_drop' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
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
