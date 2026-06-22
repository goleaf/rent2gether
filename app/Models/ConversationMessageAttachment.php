<?php

namespace App\Models;

use Database\Factories\ConversationMessageAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessageAttachment extends Model
{
    /** @use HasFactory<ConversationMessageAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_message_id',
        'conversation_id',
        'uploaded_by_user_id',
        'attachment_type',
        'media_type',
        'path',
        'thumbnail_path',
        'caption',
        'linked_type',
        'linked_id',
        'visibility',
    ];

    /**
     * Links this attachment to the message that owns it.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'conversation_message_id');
    }

    /**
     * Links this attachment to the conversation context for filtering.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this attachment to the uploading user when available.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
