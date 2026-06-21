<?php

namespace App\Models;

use Database\Factories\BookingRelocationInventoryTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationInventoryTransfer extends Model
{
    /** @use HasFactory<BookingRelocationInventoryTransferFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'booking_id',
        'inventory_item_id',
        'item_name_snapshot',
        'transfer_type',
        'status',
        'from_sleeping_place_id',
        'to_sleeping_place_id',
        'from_room_id',
        'to_room_id',
        'note',
    ];

    /**
     * Links this transfer to the relocation that requires it.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this transfer to the original booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this transfer to the old sleeping place when applicable.
     */
    public function fromSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'from_sleeping_place_id');
    }

    /**
     * Links this transfer to the new sleeping place when applicable.
     */
    public function toSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'to_sleeping_place_id');
    }

    /**
     * Links this transfer to the old room when applicable.
     */
    public function fromRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    /**
     * Links this transfer to the new room when applicable.
     */
    public function toRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }
}
