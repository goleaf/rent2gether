<?php

namespace App\Models;

use Database\Factories\ConversationMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversationMessage extends Model
{
    /** @use HasFactory<ConversationMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'message_number',
        'conversation_id',
        'sender_user_id',
        'sender_type',
        'recipient_user_id',
        'recipient_type',
        'message_type',
        'status',
        'body',
        'template_key',
        'translation_key',
        'translation_params_json',
        'source_type',
        'source_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'is_system',
        'is_important',
        'is_urgent',
        'is_pinned',
        'is_internal_note',
        'original_locale',
        'translated_locale',
        'translated_body',
        'translation_status',
        'sent_at',
        'read_at',
        'failed_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'translation_params_json' => 'array',
            'is_system' => 'boolean',
            'is_important' => 'boolean',
            'is_urgent' => 'boolean',
            'is_pinned' => 'boolean',
            'is_internal_note' => 'boolean',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'failed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Links this message to its parent conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this message to the sending user when the sender has an account.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Links this message to its recipient when it targets one user.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Links this message to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this message to the property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this message to the room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this message to the sleeping place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists attachments connected to this message.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ConversationMessageAttachment::class);
    }

    /**
     * Lists read receipts connected to this message.
     */
    public function reads(): HasMany
    {
        return $this->hasMany(ConversationMessageRead::class);
    }
}
