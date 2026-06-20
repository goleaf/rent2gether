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

    /**
     * Defines how Laravel converts stored Message attributes into PHP values.
     */
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

    /**
     * Registers lifecycle hooks that keep Message records consistent.
     */
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

    /**
     * Links this Message to the Conversation record used by its conversation relation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this Message to the Message Thread record used by its thread relation.
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    /**
     * Links this Message to the User record used by its sender relation.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Links this Message to the User record used by its sender user relation.
     */
    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Links this Message to the User record used by its recipient relation.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Links this Message to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Message to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Message to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
