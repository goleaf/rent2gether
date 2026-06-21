<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchGuestResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchGuestResponse extends Model
{
    /** @use HasFactory<BookingListingMismatchGuestResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'guest_user_id',
        'response_type',
        'message',
        'accepted_resolution_type',
        'accepted_compensation_amount',
        'accepted_refund_amount',
        'accepted_relocation_id',
    ];

    /**
     * Defines how Laravel converts stored guest response attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'accepted_compensation_amount' => 'decimal:2',
            'accepted_refund_amount' => 'decimal:2',
        ];
    }

    /**
     * Links this response to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Links this response to the guest who sent it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this response to an accepted relocation when one exists.
     */
    public function acceptedRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'accepted_relocation_id');
    }
}
