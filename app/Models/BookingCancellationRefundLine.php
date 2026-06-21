<?php

namespace App\Models;

use Database\Factories\BookingCancellationRefundLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationRefundLine extends Model
{
    /** @use HasFactory<BookingCancellationRefundLineFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_cancellation_id',
        'line_type',
        'label_key',
        'amount',
        'currency',
        'refundable',
        'refund_amount',
        'non_refundable_amount',
        'reason_key',
        'sort_order',
    ];

    /**
     * Defines how Laravel converts stored refund-line attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refundable' => 'boolean',
            'refund_amount' => 'decimal:2',
            'non_refundable_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this refund line to the parent cancellation.
     */
    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class, 'booking_cancellation_id');
    }
}
