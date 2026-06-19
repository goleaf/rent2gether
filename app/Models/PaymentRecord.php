<?php

namespace App\Models;

use App\Enums\PaymentRecordStatus;
use Database\Factories\PaymentRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRecord extends Model
{
    /** @use HasFactory<PaymentRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payer_user_id',
        'provider',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'paid_at',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentRecordStatus::class,
            'paid_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }
}
