<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Database\Factories\PayoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference', 'host_id', 'booking_id', 'stay_amount', 'service_fee', 'deductions',
    'compensation', 'net_amount', 'currency', 'payout_method', 'status',
    'scheduled_date', 'paid_date', 'delay_reason', 'notes',
])]
class Payout extends Model
{
    /** @use HasFactory<PayoutFactory> */
    use HasFactory;

    protected $casts = [
        'status' => PayoutStatus::class,
        'stay_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'deductions' => 'decimal:2',
        'compensation' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'scheduled_date' => 'date',
        'paid_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payout $payout) {
            if (empty($payout->reference)) {
                $payout->reference = strtoupper('PAY-'.now()->format('ymd').'-'.substr(uniqid(), -5));
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
