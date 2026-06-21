<?php

namespace App\Models;

use Database\Factories\BookingCheckInStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInStep extends Model
{
    /** @use HasFactory<BookingCheckInStepFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'step_key',
        'status',
        'completed_by_user_id',
        'completed_at',
        'required',
        'sort_order',
    ];

    /**
     * Defines casts for checklist step completion fields.
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this step to its check-in process.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this step to the user who completed it.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
