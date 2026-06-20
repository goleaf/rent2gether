<?php

namespace App\Models;

use App\Enums\RefundRequestStatus;
use Database\Factories\RefundRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    /** @use HasFactory<RefundRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'requested_by_user_id',
        'amount',
        'currency',
        'reason',
        'details',
        'status',
        'resolved_at',
    ];

    /**
     * Defines how Laravel converts stored Refund Request attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => RefundRequestStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this Refund Request to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Refund Request to the User record used by its requested by relation.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
