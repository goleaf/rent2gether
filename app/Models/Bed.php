<?php

namespace App\Models;

use App\Enums\BedStatus;
use App\Enums\BedType;
use App\Enums\CancellationPolicy;
use Database\Factories\BedFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'room_id', 'title', 'type', 'max_guests', 'price_per_night', 'price_weekend',
    'price_weekly', 'price_monthly', 'cleaning_fee', 'deposit', 'discount_weekly',
    'discount_monthly', 'min_nights', 'max_nights', 'cleaning_gap_hours',
    'instant_book', 'cancellation_policy', 'has_locker', 'has_outlet', 'has_lamp',
    'has_curtain', 'has_shelf', 'has_luggage_space', 'has_linen', 'has_towel',
    'description', 'status',
])]
class Bed extends Model
{
    /** @use HasFactory<BedFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'type' => BedType::class,
        'status' => BedStatus::class,
        'cancellation_policy' => CancellationPolicy::class,
        'price_per_night' => 'decimal:2',
        'price_weekend' => 'decimal:2',
        'price_weekly' => 'decimal:2',
        'price_monthly' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'deposit' => 'decimal:2',
        'instant_book' => 'boolean',
        'has_locker' => 'boolean',
        'has_outlet' => 'boolean',
        'has_lamp' => 'boolean',
        'has_curtain' => 'boolean',
        'has_shelf' => 'boolean',
        'has_luggage_space' => 'boolean',
        'has_linen' => 'boolean',
        'has_towel' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(BedAvailability::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->where('type', 'guest_to_place')->avg('overall_rating');

        return $avg ? round($avg, 1) : null;
    }

    public function reviewCount(): int
    {
        return $this->reviews()->where('type', 'guest_to_place')->count();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', BedStatus::Active);
    }

    public function scopeAvailableForPeriod(Builder $query, string $checkIn, string $checkOut): void
    {
        // Use whereDate() to strip time component — SQLite stores dates as datetime strings
        $query->whereDoesntHave('bookings', function (Builder $q) use ($checkIn, $checkOut) {
            $q->whereNotIn('status', [
                'cancelled_guest', 'cancelled_host', 'cancelled_system', 'no_show',
            ])->whereDate('check_in', '<', $checkOut)
                ->whereDate('check_out', '>', $checkIn);
        })->whereDoesntHave('availabilities', function (Builder $q) use ($checkIn, $checkOut) {
            $q->whereIn('status', ['blocked', 'maintenance', 'cleaning'])
                ->whereDate('date', '>=', $checkIn)
                ->whereDate('date', '<', $checkOut);
        });
    }

    /**
     * Calculate total price for given nights.
     *
     * @return array{subtotal: float, discount: float, cleaning_fee: float, deposit: float, total: float, nights: int}
     */
    public function calculatePrice(string $checkIn, string $checkOut): array
    {
        $nights = (int) now()->parse($checkIn)->diffInDays($checkOut);
        $pricePerNight = (float) $this->price_per_night;

        $subtotal = $pricePerNight * $nights;
        $discount = 0.0;

        if ($nights >= 30 && $this->discount_monthly > 0) {
            $discount = $subtotal * ($this->discount_monthly / 100);
        } elseif ($nights >= 7 && $this->discount_weekly > 0) {
            $discount = $subtotal * ($this->discount_weekly / 100);
        }

        $cleaningFee = (float) ($this->cleaning_fee ?? 0);
        $deposit = (float) ($this->deposit ?? 0);
        $serviceFee = round(($subtotal - $discount) * 0.05, 2); // 5% service fee
        $total = $subtotal - $discount + $cleaningFee + $deposit + $serviceFee;

        return compact('nights', 'subtotal', 'discount', 'cleaningFee', 'deposit', 'serviceFee', 'total');
    }
}
