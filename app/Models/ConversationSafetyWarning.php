<?php

namespace App\Models;

use Database\Factories\ConversationSafetyWarningFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationSafetyWarning extends Model
{
    /** @use HasFactory<ConversationSafetyWarningFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'conversation_message_id',
        'warning_key',
        'severity',
        'triggered_by_user_id',
        'visible_to_sender',
        'visible_to_recipient',
        'message_key',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_sender' => 'boolean',
            'visible_to_recipient' => 'boolean',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this warning to its conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this warning to the message that triggered it.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'conversation_message_id');
    }

    /**
     * Links this warning to the user whose message triggered it.
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
