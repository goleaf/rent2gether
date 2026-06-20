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

    /**
     * Defines how Laravel converts stored Host Guest Stay Flag attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Adds the open query filter for reusable Host Guest Stay Flag lookups.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Adds the for host query filter for reusable Host Guest Stay Flag lookups.
     */
    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $query->where('user_id', $hostId);
    }

    /**
     * Links this Host Guest Stay Flag to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Host Guest Stay Flag to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Host Guest Stay Flag to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
