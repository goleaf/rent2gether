<?php

namespace App\Models;

use Database\Factories\BookingExtensionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id', 'original_check_out', 'new_check_out', 'extra_nights',
    'extra_amount', 'discount_amount', 'total_extra', 'requires_host_approval',
    'status', 'host_reply', 'reject_reason', 'paid_at',
])]
class BookingExtension extends Model
{
    /** @use HasFactory<BookingExtensionFactory> */
    use HasFactory;

    protected $casts = [
        'original_check_out' => 'date',
        'new_check_out' => 'date',
        'extra_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_extra' => 'decimal:2',
        'requires_host_approval' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
