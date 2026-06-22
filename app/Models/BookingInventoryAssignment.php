<?php

namespace App\Models;

use Database\Factories\BookingInventoryAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingInventoryAssignment extends Model
{
    /** @use HasFactory<BookingInventoryAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'assignment_number',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'booking_relocation_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'inventory_item_id',
        'inventory_item_unit_id',
        'assignment_type',
        'status',
        'issued_at',
        'issued_by_user_id',
        'issued_by_type',
        'expected_return',
        'expected_return_at',
        'returned_at',
        'returned_to_user_id',
        'returned_condition_status',
        'condition_at_issue',
        'condition_at_return',
        'quantity',
        'guest_confirmed_received_at',
        'host_confirmed_issued_at',
        'guest_confirmed_returned_at',
        'host_confirmed_returned_at',
        'issue_note',
        'return_note',
    ];

    protected $attributes = [
        'assignment_type' => 'issued_at_check_in',
        'status' => 'planned',
        'expected_return' => false,
        'quantity' => 1,
    ];

    /**
     * Defines how stored assignment values become PHP values.
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expected_return' => 'boolean',
            'expected_return_at' => 'datetime',
            'returned_at' => 'datetime',
            'quantity' => 'decimal:2',
            'guest_confirmed_received_at' => 'datetime',
            'host_confirmed_issued_at' => 'datetime',
            'guest_confirmed_returned_at' => 'datetime',
            'host_confirmed_returned_at' => 'datetime',
        ];
    }

    /**
     * Links this assignment to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this assignment to its stay when one exists.
     */
    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    /**
     * Links this assignment to the check-in that issued the item.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this assignment to the checkout that returned or failed the item.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this assignment to the relocation that transferred the item.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this assignment to the guest who received the item.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this assignment to the host who owns the item.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this assignment to its property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this assignment to its room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this assignment to its sleeping-place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this assignment to the item issued to the guest.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Links this assignment to the specific physical unit when tracked.
     */
    public function inventoryItemUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryItemUnit::class);
    }

    /**
     * Lists item movements caused by this assignment.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
