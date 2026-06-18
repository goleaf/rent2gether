<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'reference', 'bed_id', 'guest_id', 'host_id', 'property_id', 'room_id',
    'booking_type', 'check_in', 'check_out', 'check_in_time', 'check_out_time',
    'guests_count', 'nights', 'calendar_days_count', 'price_per_night',
    'subtotal', 'discount_amount', 'cleaning_fee', 'deposit', 'service_fee',
    'tax_amount', 'city_fee_amount', 'total', 'currency', 'status',
    'payment_status', 'payment_method', 'payment_paid_at', 'payment_deadline_at',
    'requires_document_check', 'requires_phone_check', 'requires_identity_check',
    'cancellation_policy', 'refund_amount', 'refund_status', 'cancel_reason',
    'cancelled_by', 'cancellation_terms', 'guest_message', 'host_reply',
    'check_in_instructions', 'guest_checked_in_at', 'guest_checked_out_at',
    'host_confirmed_checkin_at', 'host_confirmed_checkout_at', 'free_cancel_before',
    'deposit_released_at', 'has_dispute', 'has_complaint', 'guest_review_left',
    'host_review_left',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => BookingStatus::class,
        'booking_type' => BookingType::class,
        'payment_status' => PaymentStatus::class,
        'cancellation_policy' => CancellationPolicy::class,
        'check_in' => 'date',
        'check_out' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'early_check_in' => 'boolean',
        'late_check_out' => 'boolean',
        'flexible_check_in_time' => 'boolean',
        'flexible_check_out_time' => 'boolean',
        'time_confirmation_required' => 'boolean',
        'price_per_night' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'deposit' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'city_fee_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'requires_document_check' => 'boolean',
        'requires_phone_check' => 'boolean',
        'requires_identity_check' => 'boolean',
        'has_dispute' => 'boolean',
        'has_complaint' => 'boolean',
        'guest_review_left' => 'boolean',
        'host_review_left' => 'boolean',
        'guest_checked_in_at' => 'datetime',
        'guest_checked_out_at' => 'datetime',
        'host_confirmed_checkin_at' => 'datetime',
        'host_confirmed_checkout_at' => 'datetime',
        'free_cancel_before' => 'datetime',
        'deposit_released_at' => 'datetime',
        'payment_paid_at' => 'datetime',
        'payment_deadline_at' => 'datetime',
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

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class);
    }

    public function checkinRecord(): HasOne
    {
        return $this->hasOne(CheckinRecord::class);
    }

    public function checkoutRecord(): HasOne
    {
        return $this->hasOne(CheckoutRecord::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    public function guestReview(): ?Review
    {
        return $this->reviews()->where('type', 'guest_to_place')->first();
    }

    public function hostReview(): ?Review
    {
        return $this->reviews()->where('type', 'host_to_guest')->first();
    }
}
