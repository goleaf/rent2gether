<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'reference', 'bed_id', 'guest_id', 'check_in', 'check_out', 'check_in_time',
    'check_out_time', 'guests_count', 'nights', 'price_per_night', 'subtotal',
    'discount_amount', 'cleaning_fee', 'deposit', 'service_fee', 'total',
    'currency', 'status', 'payment_status', 'cancellation_policy',
    'refund_amount', 'cancel_reason', 'cancelled_by', 'guest_message',
    'host_reply', 'check_in_instructions', 'guest_checked_in_at',
    'guest_checked_out_at', 'host_confirmed_checkin_at',
    'host_confirmed_checkout_at', 'free_cancel_before', 'deposit_released_at',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => BookingStatus::class,
        'cancellation_policy' => CancellationPolicy::class,
        'check_in' => 'date',
        'check_out' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'price_per_night' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'deposit' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'guest_checked_in_at' => 'datetime',
        'guest_checked_out_at' => 'datetime',
        'host_confirmed_checkin_at' => 'datetime',
        'host_confirmed_checkout_at' => 'datetime',
        'free_cancel_before' => 'datetime',
        'deposit_released_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->reference)) {
                $booking->reference = strtoupper('RTG-'.now()->format('ymd').'-'.substr(uniqid(), -5));
            }
        });
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', [BookingStatus::CheckedIn, BookingStatus::ActiveStay]);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('check_in', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled_guest', 'cancelled_host', 'cancelled_system', 'no_show']);
    }

    public function isCancellable(): bool
    {
        return ! $this->status->isCancelled()
            && ! in_array($this->status, [BookingStatus::CheckedIn, BookingStatus::ActiveStay, BookingStatus::Completed]);
    }

    public function canCancelFree(): bool
    {
        return $this->free_cancel_before && now()->lt($this->free_cancel_before);
    }
}
