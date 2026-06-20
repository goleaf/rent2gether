<?php

namespace App\Models;

use Database\Factories\BookingDepositDecisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDepositDecision extends Model
{
    /** @use HasFactory<BookingDepositDecisionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'deposit_amount',
        'currency',
        'decision',
        'deduction_amount',
        'return_amount',
        'reason',
        'evidence_photo_paths_json',
        'guest_comment',
        'host_comment',
        'status',
        'decided_at',
        'guest_responded_at',
        'resolved_at',
    ];

    /**
     * Defines how Laravel converts stored Booking Deposit Decision attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'deposit_amount' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'return_amount' => 'decimal:2',
            'evidence_photo_paths_json' => 'array',
            'decided_at' => 'datetime',
            'guest_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Deposit Decision to the Booking Check Out record used by its check out relation.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this Booking Deposit Decision to the Booking record used by its booking check out relation.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->checkOut();
    }

    /**
     * Links this Booking Deposit Decision to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Deposit Decision to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Deposit Decision to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
