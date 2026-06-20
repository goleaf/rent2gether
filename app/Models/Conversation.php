<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'participant_one_id', 'participant_two_id', 'booking_id', 'bed_id', 'last_message_at',
])]
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Links this Conversation to the User record used by its participant one relation.
     */
    public function participantOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_one_id');
    }

    /**
     * Links this Conversation to the User record used by its participant two relation.
     */
    public function participantTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_two_id');
    }

    /**
     * Links this Conversation to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Conversation to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    /**
     * Lists related Message records for this Conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Checks whether the given User participates in this Conversation.
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participant_one_id === $user->id || $this->participant_two_id === $user->id;
    }

    /**
     * Links this Conversation to the participant opposite the given User.
     */
    public function otherParticipant(User $user): BelongsTo
    {
        return $this->participant_one_id === $user->id
            ? $this->participantTwo()
            : $this->participantOne();
    }

    /**
     * Returns the unread message count for this Conversation.
     */
    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
