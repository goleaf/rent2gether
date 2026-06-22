<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'participant_one_id',
        'participant_two_id',
        'booking_id',
        'bed_id',
        'conversation_number',
        'conversation_type',
        'status',
        'guest_user_id',
        'host_user_id',
        'host_representative_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'listing_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'booking_extension_id',
        'booking_relocation_id',
        'booking_cancellation_id',
        'booking_no_show_id',
        'host_unresponsive_case_id',
        'listing_mismatch_report_id',
        'complaint_case_id',
        'dispute_case_id',
        'booking_deposit_id',
        'maintenance_request_id',
        'inventory_issue_id',
        'cleaning_task_id',
        'inspection_task_id',
        'last_message_id',
        'last_message_at',
        'last_message_sender_user_id',
        'guest_unread_count',
        'host_unread_count',
        'has_urgent_messages',
        'has_important_messages',
        'guest_can_write',
        'host_can_write',
        'is_read_only',
        'is_system_only',
        'closed_at',
        'archived_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'has_urgent_messages' => 'boolean',
        'has_important_messages' => 'boolean',
        'guest_can_write' => 'boolean',
        'host_can_write' => 'boolean',
        'is_read_only' => 'boolean',
        'is_system_only' => 'boolean',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
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
     * Links this conversation to the active stay context when it exists.
     */
    public function bookingStay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class);
    }

    /**
     * Links this conversation to the check-in workflow context.
     */
    public function bookingCheckIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class);
    }

    /**
     * Links this conversation to the check-out workflow context.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class);
    }

    /**
     * Links this Conversation to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    /**
     * Links this conversation to its guest participant snapshot.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this conversation to its host participant snapshot.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this conversation to the property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this conversation to the room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this conversation to the sleeping place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this conversation to the last point-24 message.
     */
    public function lastConversationMessage(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'last_message_id');
    }

    /**
     * Lists legacy Message records for existing inbox compatibility.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Lists point-24 messages attached to this conversation.
     */
    public function conversationMessages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    /**
     * Lists explicit participant records for point-24 access checks.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Lists system events shown inside this conversation.
     */
    public function systemEvents(): HasMany
    {
        return $this->hasMany(ConversationSystemEvent::class);
    }

    /**
     * Lists internal host notes linked to this conversation.
     */
    public function internalNotes(): HasMany
    {
        return $this->hasMany(ConversationInternalNote::class);
    }

    /**
     * Lists soft safety warnings raised for this conversation.
     */
    public function safetyWarnings(): HasMany
    {
        return $this->hasMany(ConversationSafetyWarning::class);
    }

    /**
     * Lists status audit rows for this conversation.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ConversationStatusLog::class);
    }

    /**
     * Lists timeline events for this conversation.
     */
    public function events(): HasMany
    {
        return $this->hasMany(ConversationEvent::class);
    }

    /**
     * Checks whether the given user participates in this conversation.
     */
    public function hasParticipant(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ((int) $this->participant_one_id === (int) $userId || (int) $this->participant_two_id === (int) $userId) {
            return true;
        }

        return $this->participants()
            ->where('user_id', $userId)
            ->where('can_read', true)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Returns the participant opposite the given user for legacy inbox cards.
     */
    public function otherParticipant(User|int $user): ?User
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ((int) $this->participant_one_id === (int) $userId) {
            return $this->participantTwo;
        }

        if ((int) $this->participant_two_id === (int) $userId) {
            return $this->participantOne;
        }

        if ((int) $this->guest_user_id === (int) $userId) {
            return $this->host;
        }

        if ((int) $this->host_user_id === (int) $userId) {
            return $this->guest;
        }

        return null;
    }

    /**
     * Returns a relation to the guest or host opposite a user where legacy code expects a relation.
     */
    public function otherParticipantRelation(User $user): BelongsToRelation
    {
        return (int) $this->participant_one_id === (int) $user->id
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
