<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchMedia extends Model
{
    /** @use HasFactory<BookingListingMismatchMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
        'related_mismatch_item_id',
    ];

    protected $attributes = [
        'media_type' => 'photo',
        'visibility' => 'guest_and_host',
    ];

    /**
     * Links this media row to its parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Links this media row to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this media row to the user who uploaded it.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * Links this media row to a specific item when evidence is item-scoped.
     */
    public function relatedItem(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchItem::class, 'related_mismatch_item_id');
    }
}
