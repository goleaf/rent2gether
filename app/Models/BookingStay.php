<?php

namespace App\Models;

use Database\Factories\BookingStayFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingStay extends Model
{
    /** @use HasFactory<BookingStayFactory> */
    use HasFactory;

    protected $fillable = [
        'stay_number',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'status',
        'check_in_date',
        'check_in_time',
        'actual_check_in_at',
        'planned_check_out_date',
        'planned_check_out_time',
        'actual_check_out_at',
        'nights_count',
        'calendar_presence_days_count',
        'nights_passed',
        'nights_remaining',
        'payment_status',
        'deposit_status',
        'cleaning_status',
        'inspection_status',
        'has_open_complaint',
        'has_open_maintenance',
        'has_neighbor_problem',
        'has_payment_problem',
        'has_deposit_issue',
        'extension_requested',
        'relocation_requested',
        'checkout_soon',
        'checkout_required',
        'guest_note',
        'host_note',
        'internal_host_note',
        'started_at',
        'ended_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'actual_check_in_at' => 'datetime',
            'planned_check_out_date' => 'date:Y-m-d',
            'actual_check_out_at' => 'datetime',
            'nights_count' => 'integer',
            'calendar_presence_days_count' => 'integer',
            'nights_passed' => 'integer',
            'nights_remaining' => 'integer',
            'has_open_complaint' => 'boolean',
            'has_open_maintenance' => 'boolean',
            'has_neighbor_problem' => 'boolean',
            'has_payment_problem' => 'boolean',
            'has_deposit_issue' => 'boolean',
            'extension_requested' => 'boolean',
            'relocation_requested' => 'boolean',
            'checkout_soon' => 'boolean',
            'checkout_required' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function occupants(): HasMany
    {
        return $this->hasMany(BookingStayOccupant::class);
    }

    public function visibilityPreference(): HasOne
    {
        return $this->hasOne(StayVisibilityPreference::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStayStatusLog::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BookingStayNote::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingStayEvent::class);
    }

    public function checkOut(): HasOne
    {
        return $this->hasOne(BookingCheckOut::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class);
    }

    /**
     * Lists relocation records that were created from this active stay.
     */
    public function relocations(): HasMany
    {
        return $this->hasMany(BookingRelocation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::activeStatuses());
    }

    public function scopeForGuest(Builder $query, User|int $guest): Builder
    {
        $guestId = $guest instanceof User ? $guest->id : $guest;

        return $query->where('guest_user_id', $guestId);
    }

    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $hostId = $host instanceof User ? $host->id : $host;

        return $query->where('host_user_id', $hostId);
    }

    public function scopeForRoom(Builder $query, Room|int $room): Builder
    {
        $roomId = $room instanceof Room ? $room->id : $room;

        return $query->where('room_id', $roomId);
    }

    public function scopeCheckoutSoon(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('checkout_soon', true)
                ->orWhere('status', 'checkout_soon');
        });
    }

    /**
     * @return list<string>
     */
    public static function activeStatuses(): array
    {
        return [
            'active',
            'active_with_warning',
            'extension_requested',
            'extension_approved',
            'relocation_requested',
            'relocation_scheduled',
            'checkout_soon',
            'checkout_started',
            'problem_reported',
            'disputed',
        ];
    }
}
