<?php

namespace App\Models;

use Database\Factories\HostGuestStayNoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostGuestStayNote extends Model
{
    /** @use HasFactory<HostGuestStayNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_user_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'note',
        'importance',
        'is_pinned',
    ];

    protected $attributes = [
        'importance' => 'normal',
        'is_pinned' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
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

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
