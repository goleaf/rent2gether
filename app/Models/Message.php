<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'thread_id',
        'sender_id',
        'sender_user_id',
        'recipient_user_id',
        'booking_id',
        'property_id',
        'sleeping_place_id',
        'body',
        'attachment',
        'attachment_type',
        'attachments',
        'attachments_json',
        'is_system_message',
        'is_important',
        'important',
        'system_message',
        'locale',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'attachments_json' => 'array',
            'is_system_message' => 'boolean',
            'is_important' => 'boolean',
            'important' => 'boolean',
            'system_message' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Message $message): void {
            $message->sender_user_id ??= $message->sender_id;
            $message->sender_id ??= $message->sender_user_id;
            $message->attachments_json ??= $message->attachments;
            $message->attachments ??= $message->attachments_json;
            $message->is_important = $message->is_important || (bool) $message->important;
            $message->important = $message->important || (bool) $message->is_important;
            $message->is_system_message = $message->is_system_message || (bool) $message->system_message;
            $message->system_message = $message->system_message || (bool) $message->is_system_message;
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
