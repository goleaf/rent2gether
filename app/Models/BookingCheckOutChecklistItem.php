<?php

namespace App\Models;

use Database\Factories\BookingCheckOutChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutChecklistItem extends Model
{
    /** @use HasFactory<BookingCheckOutChecklistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'item_key',
        'label_key',
        'status',
        'required',
        'completed_by_user_id',
        'completed_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'completed_at' => 'datetime',
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
