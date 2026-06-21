<?php

namespace App\Models;

use Database\Factories\HostUnresponsiveRepresentativeResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsiveRepresentativeResponse extends Model
{
    /** @use HasFactory<HostUnresponsiveRepresentativeResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'host_unresponsive_case_id',
        'booking_id',
        'host_representative_id',
        'representative_user_id',
        'response_type',
        'message',
        'will_meet_guest',
        'estimated_arrival_time',
        'access_help_provided',
        'keys_handed_over',
        'guest_checked_in',
    ];

    protected $attributes = [
        'will_meet_guest' => false,
        'access_help_provided' => false,
        'keys_handed_over' => false,
        'guest_checked_in' => false,
    ];

    protected function casts(): array
    {
        return [
            'will_meet_guest' => 'boolean',
            'access_help_provided' => 'boolean',
            'keys_handed_over' => 'boolean',
            'guest_checked_in' => 'boolean',
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

    public function hostRepresentative(): BelongsTo
    {
        return $this->belongsTo(HostRepresentative::class);
    }

    public function representativeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_user_id');
    }
}
