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

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
