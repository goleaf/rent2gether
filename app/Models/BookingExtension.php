<?php

namespace App\Models;

use App\Enums\BookingExtensionStatus;
use Database\Factories\BookingExtensionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'current_checkout_date',
    'requested_new_checkout_date',
    'additional_nights',
    'additional_amount',
    'original_check_out',
    'new_check_out',
    'extra_nights',
    'extra_amount',
    'discount_amount',
    'total_extra',
    'new_total',
    'payment_required',
    'payment_deadline_at',
    'requires_host_approval',
    'guest_message',
    'status',
    'host_reply',
    'host_response',
    'reject_reason',
    'paid_at',
    'approved_at',
    'declined_at',
    'cancelled_at',
])]
class BookingExtension extends Model
{
    /** @use HasFactory<BookingExtensionFactory> */
    use HasFactory;

    protected $casts = [
        'current_checkout_date' => 'date',
        'requested_new_checkout_date' => 'date',
        'original_check_out' => 'date',
        'new_check_out' => 'date',
        'additional_amount' => 'decimal:2',
        'extra_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_extra' => 'decimal:2',
        'new_total' => 'decimal:2',
        'payment_required' => 'boolean',
        'payment_deadline_at' => 'datetime',
        'requires_host_approval' => 'boolean',
        'status' => BookingExtensionStatus::class,
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'declined_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Registers lifecycle hooks that keep Booking Extension records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (BookingExtension $extension): void {
            $extension->original_check_out ??= $extension->current_checkout_date;
            $extension->current_checkout_date ??= $extension->original_check_out;
            $extension->new_check_out ??= $extension->requested_new_checkout_date;
            $extension->requested_new_checkout_date ??= $extension->new_check_out;
            $extension->extra_nights = $extension->extra_nights ?: $extension->additional_nights;
            $extension->additional_nights = $extension->additional_nights ?: $extension->extra_nights;
            $extension->extra_amount = $extension->extra_amount ?: $extension->additional_amount;
            $extension->additional_amount = $extension->additional_amount ?: $extension->extra_amount;
            $extension->host_reply ??= $extension->host_response;
            $extension->host_response ??= $extension->host_reply;
        });
    }

    /**
     * Links this Booking Extension to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Returns the status label text for this Booking Extension.
     */
    public function statusLabel(): string
    {
        return $this->status instanceof BookingExtensionStatus
            ? $this->status->label()
            : __('statuses.extension.'.(string) $this->status);
    }
}
