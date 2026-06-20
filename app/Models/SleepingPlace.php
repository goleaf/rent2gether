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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SleepingPlace extends Model
{
    /** @use HasFactory<SleepingPlaceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'property_id',
        'type',
        'sleeping_place_type',
        'sleeping_place_subtype',
        'status',
        'place_number',
        'display_name',
        'internal_name',
        'bunk_level',
        'is_top_bunk',
        'is_bottom_bunk',
        'is_single',
        'is_double',
        'is_for_one_person',
        'is_for_couple',
        'length_cm',
        'width_cm',
        'height_cm',
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
        'has_hook',
        'has_locker',
        'locker_has_lock',
        'has_luggage_space',
        'near_window',
        'near_door',
        'near_radiator',
        'near_air_conditioner',
        'privacy_level',
        'noise_level',
        'is_accessible',
        'suitable_for_tall_person',
        'suitable_for_elderly',
        'suitable_for_limited_mobility',
        'max_guests',
        'min_guest_age',
        'max_guest_age',
        'sort_order',
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
        'extensions_allowed',
        'can_extend',
        'early_check_in_allowed',
        'late_check_out_allowed',
        'second_guest_allowed',
        'second_guest_fee',
        'cancellation_policy',
    ];

    protected function casts(): array
    {
        return [
            'type' => SleepingPlaceType::class,
            'sleeping_place_type' => SleepingPlaceType::class,
            'status' => SleepingPlaceStatus::class,
            'is_top_bunk' => 'boolean',
            'is_bottom_bunk' => 'boolean',
            'is_single' => 'boolean',
            'is_double' => 'boolean',
            'is_for_one_person' => 'boolean',
            'is_for_couple' => 'boolean',
            'has_pillow' => 'boolean',
            'has_blanket' => 'boolean',
            'has_bedding' => 'boolean',
            'has_towel' => 'boolean',
            'has_curtain' => 'boolean',
            'has_lamp' => 'boolean',
            'has_power_socket' => 'boolean',
            'has_usb' => 'boolean',
            'has_shelf' => 'boolean',
            'has_hook' => 'boolean',
            'has_locker' => 'boolean',
            'locker_has_lock' => 'boolean',
            'has_luggage_space' => 'boolean',
            'near_window' => 'boolean',
            'near_door' => 'boolean',
            'near_radiator' => 'boolean',
            'near_air_conditioner' => 'boolean',
            'is_accessible' => 'boolean',
            'suitable_for_tall_person' => 'boolean',
            'suitable_for_elderly' => 'boolean',
            'suitable_for_limited_mobility' => 'boolean',
            'sort_order' => 'integer',
            'base_price_per_night' => 'decimal:2',
            'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'holiday_price' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'instant_booking_enabled' => 'boolean',
            'requires_host_approval' => 'boolean',
            'extensions_allowed' => 'boolean',
            'can_extend' => 'boolean',
            'early_check_in_allowed' => 'boolean',
            'late_check_out_allowed' => 'boolean',
            'second_guest_allowed' => 'boolean',
            'second_guest_fee' => 'decimal:2',
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

    public function physicalDetails(): HasOne
    {
        return $this->hasOne(SleepingPlacePhysicalDetail::class);
    }

    public function comfortDetails(): HasOne
    {
        return $this->hasOne(SleepingPlaceComfortDetail::class);
    }

    public function storageDetails(): HasOne
    {
        return $this->hasOne(SleepingPlaceStorageDetail::class);
    }

    public function positionDetails(): HasOne
    {
        return $this->hasOne(SleepingPlacePositionDetail::class);
    }

    public function conditionDetails(): HasOne
    {
        return $this->hasOne(SleepingPlaceConditionDetail::class);
    }

    public function compatibilityProfile(): HasOne
    {
        return $this->hasOne(SleepingPlaceCompatibilityProfile::class);
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

    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function waitlistItems(): HasMany
    {
        return $this->hasMany(WaitlistItem::class);
    }

    public function occupantSnapshots(): HasMany
    {
        return $this->hasMany(RoomOccupantSnapshot::class);
    }

    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(CompatibilityResult::class);
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

    public function cardMedia(): MorphOne
    {
        return $this->morphOne(MediaItem::class, 'mediable')
            ->active()
            ->orderByDesc('is_primary')
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), SleepingPlaceStatus::Active);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), SleepingPlaceStatus::Active);
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

    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where($query->qualifyColumn('room_id'), $roomId);
    }

    public function scopeForProperty(Builder $query, int $propertyId): Builder
    {
        return $query->where($query->qualifyColumn('property_id'), $propertyId);
    }

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->whereHas('bookings', fn (Builder $booking) => $booking->where('guest_user_id', $userId));
    }

    public function scopeByType(Builder $query, SleepingPlaceType|string $type): Builder
    {
        $value = $type instanceof SleepingPlaceType ? $type->value : $type;

        return $query->where(function (Builder $builder) use ($value): void {
            $builder->where($builder->qualifyColumn('sleeping_place_type'), $value)
                ->orWhere($builder->qualifyColumn('type'), $value);
        });
    }

    public function scopeTopBunk(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('is_top_bunk'), true)
                ->orWhere($builder->qualifyColumn('bunk_level'), 'top')
                ->orWhere($builder->qualifyColumn('type'), SleepingPlaceType::BunkTop->value);
        });
    }

    public function scopeBottomBunk(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('is_bottom_bunk'), true)
                ->orWhere($builder->qualifyColumn('bunk_level'), 'bottom')
                ->orWhere($builder->qualifyColumn('type'), SleepingPlaceType::BunkBottom->value);
        });
    }

    public function scopeWithLocker(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_locker'), true)
                ->orWhereHas('storageDetails', fn (Builder $details) => $details->where('has_personal_locker', true));
        });
    }

    public function scopeWithCurtain(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_curtain'), true)
                ->orWhereHas('positionDetails', fn (Builder $details) => $details->where('has_curtain', true));
        });
    }

    public function scopeWithPowerSocket(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_power_socket'), true)
                ->orWhereHas('positionDetails', fn (Builder $details) => $details->where('has_power_socket', true));
        });
    }

    public function scopeWithBedding(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_bedding'), true)
                ->orWhereHas('comfortDetails', fn (Builder $details) => $details->where('has_bedding', true));
        });
    }

    public function scopeWithTowel(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_towel'), true)
                ->orWhereHas('comfortDetails', fn (Builder $details) => $details->where('has_towel', true));
        });
    }

    public function scopeSuitableForTallPerson(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('suitable_for_tall_person'), true)
                ->orWhereHas('physicalDetails', fn (Builder $details) => $details->where('suitable_for_tall_person', true));
        });
    }

    public function scopeSuitableForHeavyPerson(Builder $query): Builder
    {
        return $query->whereHas('physicalDetails', fn (Builder $details) => $details->where('suitable_for_heavy_person', true));
    }

    public function scopeSuitableForCouple(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('is_for_couple'), true)
                ->orWhere($builder->qualifyColumn('max_guests'), '>=', 2);
        });
    }

    public function scopeInstantBooking(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('instant_booking_enabled'), true);
    }

    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query
            ->active()
            ->whereHas('room', fn (Builder $room) => $room->active())
            ->whereHas('property', fn (Builder $property) => $property->active())
            ->whereDoesntHave('bookings', function (Builder $booking) use ($start, $end): void {
                $booking->whereNotIn('status', [
                    BookingStatus::Draft->value,
                    BookingStatus::DeclinedByHost->value,
                    BookingStatus::CancelledByGuestFlow->value,
                    BookingStatus::CancelledByHostFlow->value,
                    BookingStatus::Expired->value,
                    BookingStatus::CancelledByGuest->value,
                    BookingStatus::CancelledByHost->value,
                    BookingStatus::CancelledBySystem->value,
                    BookingStatus::CancelledByService->value,
                    BookingStatus::NoShow->value,
                    BookingStatus::HostNoShow->value,
                    BookingStatus::CheckedOut->value,
                    BookingStatus::Completed->value,
                    BookingStatus::AwaitingReview->value,
                    BookingStatus::Closed->value,
                ])->whereDate('check_in_date', '<', $end)
                    ->whereDate('check_out_date', '>', $start);
            })
            ->whereDoesntHave('availabilityDays', function (Builder $day) use ($start, $end): void {
                $day->whereIn('status', AvailabilityStatus::blocksStayValues())
                    ->whereDate('date', '>=', $start)
                    ->whereDate('date', '<', $end);
            })
            ->whereDoesntHave('availabilityDays', function (Builder $day) use ($start): void {
                $day->whereDate('date', $start)
                    ->where(function (Builder $query): void {
                        $query->where('check_in_allowed', false)
                            ->orWhere('status', AvailabilityStatus::CheckOutOnly->value);
                    });
            })
            ->whereDoesntHave('availabilityDays', function (Builder $day) use ($end): void {
                $day->whereDate('date', $end)
                    ->where(function (Builder $query): void {
                        $query->where('check_out_allowed', false)
                            ->orWhere('status', AvailabilityStatus::CheckInOnly->value);
                    });
            });
    }
}
