<?php

namespace App\Models;

use Database\Factories\ConversationSystemEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationSystemEvent extends Model
{
    /** @use HasFactory<ConversationSystemEventFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'conversation_message_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'translation_key',
        'translation_params_json',
        'importance_level',
        'created_by_user_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'translation_params_json' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * Links this system event to its conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this event to the visible system message when one was created.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'conversation_message_id');
    }

    /**
     * Links this event to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
