<?php

namespace App\Models;

use Database\Factories\HostCurrentStaySnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCurrentStaySnapshot extends Model
{
    /** @use HasFactory<HostCurrentStaySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_user_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'guest_display_name',
        'guest_avatar_url',
        'room_label',
        'sleeping_place_label',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'nights_count',
        'nights_left',
        'payment_status',
        'stay_status',
        'check_in_status',
        'payout_status',
        'booking_total_amount',
        'paid_amount',
        'remaining_amount',
        'deposit_amount',
        'cleaning_fee_amount',
        'has_special_requests',
        'special_requests_summary',
        'guest_rating_average',
        'roommate_rating_average',
        'has_complaints',
        'open_complaints_count',
        'needs_extension',
        'extension_requested_at',
        'needs_checkout',
        'checkout_due_today',
        'checkout_overdue',
        'needs_cleaning_after_checkout',
        'needs_inspection',
        'needs_repair',
        'last_host_note',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'nights_count' => 'integer',
            'nights_left' => 'integer',
            'booking_total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'cleaning_fee_amount' => 'decimal:2',
            'has_special_requests' => 'boolean',
            'guest_rating_average' => 'decimal:2',
            'roommate_rating_average' => 'decimal:2',
            'has_complaints' => 'boolean',
            'open_complaints_count' => 'integer',
            'needs_extension' => 'boolean',
            'extension_requested_at' => 'datetime',
            'needs_checkout' => 'boolean',
            'checkout_due_today' => 'boolean',
            'checkout_overdue' => 'boolean',
            'needs_cleaning_after_checkout' => 'boolean',
            'needs_inspection' => 'boolean',
            'needs_repair' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $query->where('user_id', $hostId);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->host();
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
