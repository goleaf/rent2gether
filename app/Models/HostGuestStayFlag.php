<?php

namespace App\Models;

use Database\Factories\HostGuestStayFlagFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostGuestStayFlag extends Model
{
    /** @use HasFactory<HostGuestStayFlagFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_user_id',
        'booking_id',
        'flag_key',
        'status',
        'severity',
        'message_key',
        'message_params_json',
        'resolved_at',
    ];

    protected $attributes = [
        'status' => 'open',
        'severity' => 'medium',
    ];

    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $query->where('user_id', $hostId);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
