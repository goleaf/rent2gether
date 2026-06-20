<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Database\Factories\AvailabilityDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityDay extends Model
{
    /** @use HasFactory<AvailabilityDayFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'booking_id',
        'date',
        'status',
        'price_override',
        'min_nights_override',
        'max_nights_override',
        'check_in_allowed',
        'check_out_allowed',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AvailabilityStatus::class,
            'price_override' => 'decimal:2',
            'check_in_allowed' => 'boolean',
            'check_out_allowed' => 'boolean',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
