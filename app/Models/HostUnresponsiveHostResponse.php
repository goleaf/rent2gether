<?php

namespace App\Models;

use Database\Factories\HostUnresponsiveHostResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsiveHostResponse extends Model
{
    /** @use HasFactory<HostUnresponsiveHostResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'host_unresponsive_case_id',
        'booking_id',
        'host_user_id',
        'response_type',
        'message',
        'instruction_resent',
        'access_details_provided',
        'new_arrival_time_proposed',
        'representative_assigned',
        'alternative_sleeping_place_id',
    ];

    protected $attributes = [
        'instruction_resent' => false,
        'access_details_provided' => false,
        'representative_assigned' => false,
    ];

    protected function casts(): array
    {
        return [
            'instruction_resent' => 'boolean',
            'access_details_provided' => 'boolean',
            'representative_assigned' => 'boolean',
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

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function alternativeSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'alternative_sleeping_place_id');
    }
}
