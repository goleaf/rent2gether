<?php

namespace App\Models;

use App\Enums\MessageThreadType;
use Database\Factories\MessageThreadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    /** @use HasFactory<MessageThreadFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'guest_user_id',
        'host_user_id',
        'booking_id',
        'property_id',
        'sleeping_place_id',
        'last_message_at',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Message Thread attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'type' => MessageThreadType::class,
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Links this Message Thread to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Message Thread to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Message Thread to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Message Thread to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Message Thread to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists related Message records for this Message Thread.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'thread_id');
    }

    /**
     * Adds the for guest query filter for reusable Message Thread lookups.
     */
    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('guest_user_id', $userId);
    }

    /**
     * Adds the for host query filter for reusable Message Thread lookups.
     */
    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->where('host_user_id', $userId);
    }

    /**
     * Adds the for participant query filter for reusable Message Thread lookups.
     */
    public function scopeForParticipant(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId): void {
            $query->where('guest_user_id', $userId)
                ->orWhere('host_user_id', $userId);
        });
    }

    /**
     * Checks whether the given User participates in this Message Thread.
     */
    public function hasParticipant(User $user): bool
    {
        return (int) $this->guest_user_id === (int) $user->id
            || (int) $this->host_user_id === (int) $user->id;
    }

    /**
     * Returns the participant opposite the given User in this Message Thread.
     */
    public function otherParticipant(User $user): ?User
    {
        if ((int) $this->guest_user_id === (int) $user->id) {
            return $this->host;
        }

        if ((int) $this->host_user_id === (int) $user->id) {
            return $this->guest;
        }

        return null;
    }

    /**
     * Returns the unread message count for the given User in this Message Thread.
     */
    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where(function (Builder $query) use ($user): void {
                $query->where('recipient_user_id', $user->id)
                    ->orWhere(function (Builder $query) use ($user): void {
                        $query->whereNull('recipient_user_id')
                            ->where('sender_id', '!=', $user->id);
                    });
            })
            ->whereNull('read_at')
            ->count();
    }
}
