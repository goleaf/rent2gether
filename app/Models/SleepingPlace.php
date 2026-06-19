<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use Database\Factories\SleepingPlaceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SleepingPlace extends Model
{
    /** @use HasFactory<SleepingPlaceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'property_id',
        'type',
        'status',
        'place_number',
        'bunk_level',
        'length_cm',
        'width_cm',
        'mattress_type',
        'mattress_firmness',
        'has_pillow',
        'has_blanket',
        'has_bedding',
        'has_towel',
        'has_curtain',
        'has_lamp',
        'has_power_socket',
        'has_usb',
        'has_shelf',
        'has_locker',
        'locker_has_lock',
        'has_luggage_space',
        'is_accessible',
        'suitable_for_tall_person',
        'suitable_for_elderly',
        'max_guests',
        'min_guest_age',
        'max_guest_age',
        'base_price_per_night',
        'weekly_price',
        'monthly_price',
        'weekend_price',
        'holiday_price',
        'cleaning_fee',
        'deposit_amount',
        'currency',
        'min_nights',
        'max_nights',
        'instant_booking_enabled',
        'requires_host_approval',
    ];

    protected function casts(): array
    {
        return [
            'type' => SleepingPlaceType::class,
            'status' => SleepingPlaceStatus::class,
            'has_pillow' => 'boolean',
            'has_blanket' => 'boolean',
            'has_bedding' => 'boolean',
            'has_towel' => 'boolean',
            'has_curtain' => 'boolean',
            'has_lamp' => 'boolean',
            'has_power_socket' => 'boolean',
            'has_usb' => 'boolean',
            'has_shelf' => 'boolean',
            'has_locker' => 'boolean',
            'locker_has_lock' => 'boolean',
            'has_luggage_space' => 'boolean',
            'is_accessible' => 'boolean',
            'suitable_for_tall_person' => 'boolean',
            'suitable_for_elderly' => 'boolean',
            'base_price_per_night' => 'decimal:2',
            'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'holiday_price' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'instant_booking_enabled' => 'boolean',
            'requires_host_approval' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SleepingPlaceTranslation::class);
    }

    public function availabilityDays(): HasMany
    {
        return $this->hasMany(AvailabilityDay::class);
    }

    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class);
    }

    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'sleeping_place_amenity')->withTimestamps();
    }

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'sleeping_place_rule')->withTimestamps();
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SleepingPlaceStatus::Active);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', SleepingPlaceStatus::Active);
    }

    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->whereHas('property', fn (Builder $property) => $property->where('city_id', $cityId));
    }

    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->whereHas('property', fn (Builder $property) => $property->where('host_user_id', $userId));
    }

    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query
            ->active()
            ->whereDoesntHave('bookings', function (Builder $booking) use ($start, $end): void {
                $booking->whereNotIn('status', [
                    BookingStatus::CancelledByGuest->value,
                    BookingStatus::CancelledByHost->value,
                    BookingStatus::CancelledBySystem->value,
                    BookingStatus::CancelledByService->value,
                    BookingStatus::NoShow->value,
                ])->whereDate('check_in_date', '<', $end)
                    ->whereDate('check_out_date', '>', $start);
            })
            ->whereDoesntHave('availabilityDays', function (Builder $day) use ($start, $end): void {
                $day->whereIn('status', [
                    AvailabilityStatus::Blocked->value,
                    AvailabilityStatus::Maintenance->value,
                    AvailabilityStatus::Cleaning->value,
                ])->whereDate('date', '>=', $start)
                    ->whereDate('date', '<', $end);
            });
    }
}
