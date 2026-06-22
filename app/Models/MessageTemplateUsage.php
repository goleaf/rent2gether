<?php

namespace App\Models;

use Database\Factories\MessageTemplateUsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplateUsage extends Model
{
    /** @use HasFactory<MessageTemplateUsageFactory> */
    use HasFactory;

    protected $fillable = [
        'message_template_id',
        'template_key',
        'conversation_id',
        'conversation_message_id',
        'user_id',
        'booking_id',
        'source_type',
        'source_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    /**
     * Links this usage row to the template that was used.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    /**
     * Links this usage row to the conversation where it happened.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this usage row to the message created from the template.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'conversation_message_id');
    }

    /**
     * Links this usage row to the user who selected the template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this usage row to the booking context when available.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
