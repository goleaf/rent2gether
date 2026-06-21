<?php

namespace App\Models;

use Database\Factories\BookingRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequirement extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_WAIVED = 'waived';

    public const STATUS_NOT_REQUIRED = 'not_required';

    /** @use HasFactory<BookingRequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'requirement_key',
        'status',
        'required',
        'completed_at',
        'message_key',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'required' => true,
    ];

    /**
     * Defines how Laravel converts stored Booking Requirement attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Links this requirement to the Booking it can block.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
