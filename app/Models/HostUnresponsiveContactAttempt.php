<?php

namespace App\Models;

use Database\Factories\HostUnresponsiveContactAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsiveContactAttempt extends Model
{
    /** @use HasFactory<HostUnresponsiveContactAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'host_unresponsive_case_id',
        'booking_id',
        'target_user_id',
        'target_type',
        'target_name_snapshot',
        'target_contact_snapshot',
        'contact_channel',
        'attempt_type',
        'status',
        'message_key',
        'message_text',
        'attempted_at',
        'response_received_at',
        'response_summary',
    ];

    protected $attributes = [
        'status' => 'created',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'response_received_at' => 'datetime',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(BookingHostUnresponsiveCase::class, 'host_unresponsive_case_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
