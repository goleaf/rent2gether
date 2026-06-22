<?php

namespace App\Models;

use Database\Factories\ConversationInternalNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationInternalNote extends Model
{
    /** @use HasFactory<ConversationInternalNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'booking_id',
        'booking_stay_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'note',
        'note_type',
        'created_by_user_id',
        'visible_to_host',
        'visible_to_guest',
        'internal',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_host' => 'boolean',
            'visible_to_guest' => 'boolean',
            'internal' => 'boolean',
        ];
    }

    /**
     * Links this internal note to its conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Links this internal note to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this internal note to the host who owns it.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this internal note to the user who created it.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
