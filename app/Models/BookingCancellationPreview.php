<?php

namespace App\Models;

use Database\Factories\BookingCancellationPreviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationPreview extends Model
{
    /** @use HasFactory<BookingCancellationPreviewFactory> */
    use HasFactory;

    protected $fillable = [
        'preview_number',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'requested_by_user_id',
        'requested_by_type',
        'cancellation_type',
        'reason_key',
        'comment',
        'check_in_date',
        'check_out_date',
        'cancelled_at_preview',
        'hours_before_check_in',
        'nights_before_check_in',
        'nights_used',
        'nights_unused',
        'accommodation_amount',
        'cleaning_fee_amount',
        'service_fee_amount',
        'deposit_amount',
        'tax_amount',
        'city_fee_amount',
        'accommodation_refund_amount',
        'cleaning_fee_refund_amount',
        'service_fee_refund_amount',
        'deposit_refund_amount',
        'tax_refund_amount',
        'city_fee_refund_amount',
        'penalty_amount',
        'host_payout_adjustment_amount',
        'total_refund_amount',
        'total_non_refundable_amount',
        'currency',
        'policy_snapshot_json',
        'refund_breakdown_json',
        'expires_at',
        'status',
    ];

    /**
     * Defines how Laravel converts stored cancellation-preview attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'cancelled_at_preview' => 'datetime',
            'hours_before_check_in' => 'integer',
            'nights_before_check_in' => 'integer',
            'nights_used' => 'integer',
            'nights_unused' => 'integer',
            'accommodation_amount' => 'decimal:2',
            'cleaning_fee_amount' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'city_fee_amount' => 'decimal:2',
            'accommodation_refund_amount' => 'decimal:2',
            'cleaning_fee_refund_amount' => 'decimal:2',
            'service_fee_refund_amount' => 'decimal:2',
            'deposit_refund_amount' => 'decimal:2',
            'tax_refund_amount' => 'decimal:2',
            'city_fee_refund_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'host_payout_adjustment_amount' => 'decimal:2',
            'total_refund_amount' => 'decimal:2',
            'total_non_refundable_amount' => 'decimal:2',
            'policy_snapshot_json' => 'array',
            'refund_breakdown_json' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Links this preview to the Booking being cancelled.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this preview to the guest receiving the refund preview.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this preview to the host affected by the cancellation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this preview to the user who requested it.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * Links this preview to the copied Property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this preview to the copied Room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this preview to the exact SleepingPlace being cancelled.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
