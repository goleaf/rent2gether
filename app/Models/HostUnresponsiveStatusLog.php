<?php

namespace App\Models;

use Database\Factories\HostUnresponsiveStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsiveStatusLog extends Model
{
    /** @use HasFactory<HostUnresponsiveStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'host_unresponsive_case_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'context_json' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
