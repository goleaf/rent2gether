<?php

namespace App\Models;

use Database\Factories\BookingCheckOutStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutStep extends Model
{
    /** @use HasFactory<BookingCheckOutStepFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'step_key',
        'status',
        'required',
        'completed_by_user_id',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'completed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
