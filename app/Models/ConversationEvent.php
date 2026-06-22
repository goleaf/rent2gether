<?php

namespace App\Models;

use Database\Factories\ConversationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationEvent extends Model
{
    /** @use HasFactory<ConversationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to its conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this event to the user who caused it when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
