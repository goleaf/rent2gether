<?php

namespace App\Models;

use Database\Factories\ConversationParticipantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    /** @use HasFactory<ConversationParticipantFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'participant_type',
        'display_name_snapshot',
        'can_write',
        'can_read',
        'can_upload',
        'can_use_templates',
        'muted',
        'archived',
        'last_read_message_id',
        'last_read_at',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'can_write' => 'boolean',
            'can_read' => 'boolean',
            'can_upload' => 'boolean',
            'can_use_templates' => 'boolean',
            'muted' => 'boolean',
            'archived' => 'boolean',
            'last_read_at' => 'datetime',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    /**
     * Links this participant record to its conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this participant record to its user when the participant has an account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this participant to the last message read by the user.
     */
    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'last_read_message_id');
    }
}
