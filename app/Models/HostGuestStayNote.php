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

    /**
     * Defines how Laravel converts stored Host Guest Stay Note attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * Adds the for host query filter for reusable Host Guest Stay Note lookups.
     */
    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $query->where('user_id', $hostId);
    }

    /**
     * Links this Host Guest Stay Note to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Host Guest Stay Note to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Host Guest Stay Note to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Host Guest Stay Note to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Host Guest Stay Note to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Host Guest Stay Note to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
