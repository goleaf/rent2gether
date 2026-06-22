<?php

namespace App\Models;

use Database\Factories\ConversationMessageReadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessageRead extends Model
{
    /** @use HasFactory<ConversationMessageReadFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'conversation_message_id',
        'user_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Links this read receipt to its conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this read receipt to the message that was read.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'conversation_message_id');
    }

    /**
     * Links this read receipt to the user who read the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
