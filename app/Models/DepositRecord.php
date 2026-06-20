<?php

namespace App\Models;

use Database\Factories\DepositRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositRecord extends Model
{
    /** @use HasFactory<DepositRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'currency',
        'status',
        'held_at',
        'released_at',
        'withheld_amount',
        'withhold_reason',
    ];

    /**
     * Defines how Laravel converts stored Deposit Record attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'held_at' => 'datetime',
            'released_at' => 'datetime',
            'withheld_amount' => 'decimal:2',
        ];
    }

    /**
     * Links this Deposit Record to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
