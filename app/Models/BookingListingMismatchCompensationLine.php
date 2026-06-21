<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchCompensationLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchCompensationLine extends Model
{
    /** @use HasFactory<BookingListingMismatchCompensationLineFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'line_type',
        'label_key',
        'amount',
        'currency',
        'calculation_type',
        'percent',
        'nights_count',
        'refundable',
        'payable_to_guest',
        'deduct_from_host_payout',
        'reason_key',
        'sort_order',
    ];

    protected $attributes = [
        'amount' => 0,
        'refundable' => true,
        'payable_to_guest' => true,
        'deduct_from_host_payout' => false,
        'sort_order' => 0,
    ];

    /**
     * Defines how Laravel converts stored compensation line attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percent' => 'decimal:2',
            'nights_count' => 'integer',
            'refundable' => 'boolean',
            'payable_to_guest' => 'boolean',
            'deduct_from_host_payout' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this compensation line to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }
}
