<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingListingMismatchItem extends Model
{
    /** @use HasFactory<BookingListingMismatchItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'item_key',
        'item_type',
        'promised_value',
        'actual_value',
        'snapshot_source_type',
        'snapshot_source_id',
        'is_confirmed',
        'confidence_score',
        'severity',
        'guest_note',
        'host_note',
    ];

    protected $attributes = [
        'severity' => 'medium',
    ];

    /**
     * Defines how Laravel converts stored mismatch item attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
            'confidence_score' => 'decimal:2',
        ];
    }

    /**
     * Links this item to its parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Lists evidence media attached directly to this item.
     */
    public function media(): HasMany
    {
        return $this->hasMany(BookingListingMismatchMedia::class, 'related_mismatch_item_id');
    }
}
