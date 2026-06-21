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

    /**
     * Registers lifecycle hooks that keep Sleeping Place records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (SleepingPlace $place): void {
            if (! $place->property_id && $place->room_id) {
                $place->property_id = $place->relationLoaded('room')
                    ? $place->room?->property_id
                    : Room::query()->whereKey($place->room_id)->value('property_id');
            }

            if (! $place->user_id) {
                $place->user_id = $place->relationLoaded('property')
                    ? $place->property?->host_user_id
                    : Property::query()->whereKey($place->property_id)->value('host_user_id');
            }

            $place->title ??= $place->display_name;
            $place->display_name ??= $place->title;
            $place->place_type ??= self::foundationPlaceType($place->sleeping_place_type?->value ?? $place->type?->value ?? $place->type);
            $place->sleeping_place_type ??= self::legacyPlaceType((string) $place->place_type);
            $place->type ??= self::legacyPlaceType((string) $place->place_type);
            $place->base_price ??= $place->base_price_per_night;
            $place->base_price_per_night ??= $place->base_price;
            $place->max_guests_count = $place->max_guests_count ?: ($place->max_guests ?: 1);
            $place->max_guests = $place->max_guests ?: $place->max_guests_count;
            $place->is_double_place = (bool) ($place->is_double_place || $place->is_double);
            $place->has_mattress = (bool) ($place->has_mattress || $place->mattress_type);
            $place->has_privacy_curtain = (bool) ($place->has_privacy_curtain || $place->has_curtain);
            $place->has_personal_lamp = (bool) ($place->has_personal_lamp || $place->has_lamp);
            $place->has_socket = (bool) ($place->has_socket || $place->has_power_socket);
            $place->has_lockable_locker = (bool) ($place->has_lockable_locker || $place->locker_has_lock);
            $place->suitable_for_tall_guest = (bool) ($place->suitable_for_tall_guest || $place->suitable_for_tall_person);
            $place->suitable_for_couple = (bool) ($place->suitable_for_couple || $place->is_for_couple);
        });

        static::deleting(function (SleepingPlace $place): void {
            $place->listingHintSnapshots()->delete();
            $place->guestHintDismissals()->delete();
            $place->guestHintImpressions()->delete();
            $place->hostHintSnapshots()->delete();
            $place->hostHintDismissals()->delete();
        });
    }

    protected $fillable = [
        'room_id',
        'property_id',
        'user_id',
        'title',
        'place_type',
        'type',
        'sleeping_place_type',
        'sleeping_place_subtype',
        'bed_type',
        'status',
        'place_number',
        'display_name',
        'internal_name',
        'bunk_level',
        'is_top_bunk',
        'is_bottom_bunk',
        'is_single',
        'is_double',
        'is_double_place',
        'is_for_one_person',
        'is_for_couple',
        'length_cm',
        'width_cm',
        'height_cm',
        'mattress_type',
        'mattress_firmness',
        'mattress_condition',
        'has_mattress',
        'has_pillow',
        'has_blanket',
        'has_bedding',
        'has_towel',
        'has_curtain',
        'has_privacy_curtain',
        'has_lamp',
        'has_personal_lamp',
        'has_power_socket',
        'has_socket',
        'has_usb',
        'has_shelf',
        'has_hook',
        'has_locker',
        'locker_has_lock',
        'has_lockable_locker',
        'has_luggage_space',
        'near_window',
        'near_door',
        'near_radiator',
        'near_air_conditioner',
        'near_passage',
        'privacy_level',
        'noise_level',
        'is_accessible',
        'suitable_for_tall_person',
        'suitable_for_tall_guest',
        'suitable_for_heavy_guest',
        'suitable_for_couple',
        'suitable_for_elderly',
        'suitable_for_limited_mobility',
        'max_guests',
        'max_guests_count',
        'min_guest_age',
        'max_guest_age',
        'sort_order',
        'base_price_per_night',
        'base_price',
        'weekly_price',
        'monthly_price',
        'weekend_price',
        'holiday_price',
        'cleaning_fee',
        'deposit_amount',
        'currency',
        'min_nights',
        'max_nights',
        'cleaning_gap_days',
        'instant_booking_enabled',
        'requires_host_approval',
        'extensions_allowed',
        'can_extend',
        'early_check_in_allowed',
        'late_check_out_allowed',
        'second_guest_allowed',
        'second_guest_fee',
        'cancellation_policy',
        'publication_status',
        'completed_at',
        'published_at',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place attributes into PHP values.
     */
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
            'is_double_place' => 'boolean',
            'is_for_one_person' => 'boolean',
            'is_for_couple' => 'boolean',
            'has_mattress' => 'boolean',
            'has_pillow' => 'boolean',
            'has_blanket' => 'boolean',
            'has_bedding' => 'boolean',
            'has_towel' => 'boolean',
            'has_curtain' => 'boolean',
            'has_privacy_curtain' => 'boolean',
            'has_lamp' => 'boolean',
            'has_personal_lamp' => 'boolean',
            'has_power_socket' => 'boolean',
            'has_socket' => 'boolean',
            'has_usb' => 'boolean',
            'has_shelf' => 'boolean',
            'has_hook' => 'boolean',
            'has_locker' => 'boolean',
            'locker_has_lock' => 'boolean',
            'has_lockable_locker' => 'boolean',
            'has_luggage_space' => 'boolean',
            'near_window' => 'boolean',
            'near_door' => 'boolean',
            'near_radiator' => 'boolean',
            'near_air_conditioner' => 'boolean',
            'near_passage' => 'boolean',
            'is_accessible' => 'boolean',
            'suitable_for_tall_person' => 'boolean',
            'suitable_for_tall_guest' => 'boolean',
            'suitable_for_heavy_guest' => 'boolean',
            'suitable_for_couple' => 'boolean',
            'suitable_for_elderly' => 'boolean',
            'suitable_for_limited_mobility' => 'boolean',
            'sort_order' => 'integer',
            'base_price_per_night' => 'decimal:2',
            'base_price' => 'decimal:2',
            'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'holiday_price' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'cleaning_gap_days' => 'integer',
            'instant_booking_enabled' => 'boolean',
            'requires_host_approval' => 'boolean',
            'extensions_allowed' => 'boolean',
            'can_extend' => 'boolean',
            'early_check_in_allowed' => 'boolean',
            'late_check_out_allowed' => 'boolean',
            'second_guest_allowed' => 'boolean',
            'second_guest_fee' => 'decimal:2',
            'completed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Links this Sleeping Place to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Maps a legacy sleeping-place type into the foundation place type.
     */
    private static function foundationPlaceType(?string $type): string
    {
        return match ($type) {
            'single' => 'single_bed',
            'double' => 'double_bed',
            'bunk_top' => 'top_bunk',
            'bunk_bottom' => 'bottom_bunk',
            'fold_out' => 'folding_bed',
            'sofa_bed' => 'sofa',
            default => $type ?: 'single_bed',
        };
    }

    /**
     * Maps a foundation place type into the legacy sleeping-place enum value.
     */
    private static function legacyPlaceType(string $type): string
    {
        return match ($type) {
            'single_bed' => SleepingPlaceType::Single->value,
            'double_bed' => SleepingPlaceType::Double->value,
            'top_bunk' => SleepingPlaceType::BunkTop->value,
            'bottom_bunk' => SleepingPlaceType::BunkBottom->value,
            'folding_bed' => SleepingPlaceType::FoldOut->value,
            default => SleepingPlaceType::tryFrom($type)?->value ?? SleepingPlaceType::Other->value,
        };
    }

    /**
     * Links this Sleeping Place to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Sleeping Place to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Lists related Sleeping Place Translation records for this Sleeping Place.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SleepingPlaceTranslation::class);
    }

    /**
     * Fetches the single Sleeping Place Physical Detail record used by this Sleeping Place.
     */
    public function physicalDetails(): HasOne
    {
        return $this->hasOne(SleepingPlacePhysicalDetail::class);
    }

    /**
     * Fetches the single Sleeping Place Comfort Detail record used by this Sleeping Place.
     */
    public function comfortDetails(): HasOne
    {
        return $this->hasOne(SleepingPlaceComfortDetail::class);
    }

    /**
     * Fetches the single Sleeping Place Storage Detail record used by this Sleeping Place.
     */
    public function storageDetails(): HasOne
    {
        return $this->hasOne(SleepingPlaceStorageDetail::class);
    }

    /**
     * Fetches the single Sleeping Place Position Detail record used by this Sleeping Place.
     */
    public function positionDetails(): HasOne
    {
        return $this->hasOne(SleepingPlacePositionDetail::class);
    }

    /**
     * Fetches the single Sleeping Place Condition Detail record used by this Sleeping Place.
     */
    public function conditionDetails(): HasOne
    {
        return $this->hasOne(SleepingPlaceConditionDetail::class);
    }

    /**
     * Fetches the single Sleeping Place Compatibility Profile record used by this Sleeping Place.
     */
    public function compatibilityProfile(): HasOne
    {
        return $this->hasOne(SleepingPlaceCompatibilityProfile::class);
    }

    /**
     * Lists related Availability Day records for this Sleeping Place.
     */
    public function availabilityDays(): HasMany
    {
        return $this->hasMany(AvailabilityDay::class);
    }

    /**
     * Fetches the single Sleeping Place Calendar Setting record used by this Sleeping Place.
     */
    public function calendarSettings(): HasOne
    {
        return $this->hasOne(SleepingPlaceCalendarSetting::class);
    }

    /**
     * Fetches the single Sleeping Place Turnover Rule record used by same-day and cleaning gap checks.
     */
    public function turnoverRules(): HasOne
    {
        return $this->hasOne(SleepingPlaceTurnoverRule::class);
    }

    /**
     * Lists related Sleeping Place Calendar Day records for this Sleeping Place.
     */
    public function calendarDays(): HasMany
    {
        return $this->hasMany(SleepingPlaceCalendarDay::class);
    }

    /**
     * Lists related Sleeping Place Calendar Rule records for this Sleeping Place.
     */
    public function calendarRules(): HasMany
    {
        return $this->hasMany(SleepingPlaceCalendarRule::class);
    }

    /**
     * Lists period blocks that affect this Sleeping Place calendar.
     */
    public function calendarBlocks(): HasMany
    {
        return $this->hasMany(SleepingPlaceCalendarBlock::class);
    }

    /**
     * Lists date locks that protect this Sleeping Place from double booking.
     */
    public function bookingDateLocks(): HasMany
    {
        return $this->hasMany(SleepingPlaceBookingDateLock::class);
    }

    /**
     * Lists cancellation policies configured for this Sleeping Place.
     */
    public function cancellationPolicies(): HasMany
    {
        return $this->hasMany(SleepingPlaceCancellationPolicy::class);
    }

    /**
     * Fetches the active cancellation policy used for new Booking snapshots.
     */
    public function activeCancellationPolicy(): HasOne
    {
        return $this->hasOne(SleepingPlaceCancellationPolicy::class)->where('active', true);
    }

    /**
     * Lists no-show policies configured for this Sleeping Place.
     */
    public function noShowPolicies(): HasMany
    {
        return $this->hasMany(BookingNoShowPolicy::class);
    }

    /**
     * Fetches the active no-show policy used for new Booking snapshots.
     */
    public function activeNoShowPolicy(): HasOne
    {
        return $this->hasOne(BookingNoShowPolicy::class)->where('active', true);
    }

    /**
     * Lists host-unresponsive policies configured for this Sleeping Place.
     */
    public function hostUnresponsivePolicies(): HasMany
    {
        return $this->hasMany(HostUnresponsivePolicy::class);
    }

    /**
     * Fetches the active host-unresponsive policy used for new Booking snapshots.
     */
    public function activeHostUnresponsivePolicy(): HasOne
    {
        return $this->hasOne(HostUnresponsivePolicy::class)->where('active', true);
    }

    /**
     * Lists temporary booking quotes calculated for this Sleeping Place.
     */
    public function bookingQuotes(): HasMany
    {
        return $this->hasMany(BookingQuote::class);
    }

    /**
     * Lists booking requests submitted for this Sleeping Place.
     */
    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    /**
     * Fetches the active pricing settings used by the quote engine for this Sleeping Place.
     */
    public function pricingSettings(): HasOne
    {
        return $this->hasOne(SleepingPlacePricingSetting::class)->where('active', true);
    }

    /**
     * Lists manually configured date prices for this Sleeping Place.
     */
    public function datePrices(): HasMany
    {
        return $this->hasMany(SleepingPlaceDatePrice::class);
    }

    /**
     * Lists modern pricing discount rules used by the quote engine.
     */
    public function pricingDiscountRules(): HasMany
    {
        return $this->hasMany(SleepingPlaceDiscountRule::class);
    }

    /**
     * Lists promo codes scoped directly to this Sleeping Place.
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Lists audit entries for Sleeping Place availability status changes.
     */
    public function availabilityStatusLogs(): HasMany
    {
        return $this->hasMany(SleepingPlaceAvailabilityStatusLog::class);
    }

    /**
     * Lists related Price Rule records for this Sleeping Place.
     */
    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class);
    }

    /**
     * Lists related Discount Rule records for this Sleeping Place.
     */
    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

    /**
     * Lists related Booking records for this Sleeping Place.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingExtensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class);
    }

    /**
     * Lists related Booking Guest Intake records for this Sleeping Place.
     */
    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    /**
     * Lists related Review records for this Sleeping Place.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Lists related Favorite records for this Sleeping Place.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Lists related Waitlist Item records for this Sleeping Place.
     */
    public function waitlistItems(): HasMany
    {
        return $this->hasMany(WaitlistItem::class);
    }

    /**
     * Lists related Room Occupant Snapshot records for this Sleeping Place.
     */
    public function occupantSnapshots(): HasMany
    {
        return $this->hasMany(RoomOccupantSnapshot::class);
    }

    /**
     * Lists related Compatibility Result records for this Sleeping Place.
     */
    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(CompatibilityResult::class);
    }

    /**
     * Lists related Listing Publication Check records for this Sleeping Place.
     */
    public function publicationChecks(): HasMany
    {
        return $this->hasMany(ListingPublicationCheck::class);
    }

    /**
     * Lists related Listing Readiness Check records for this Sleeping Place.
     */
    public function readinessChecks(): HasMany
    {
        return $this->hasMany(ListingReadinessCheck::class);
    }

    /**
     * Lists related Listing Hint Snapshot records for this Sleeping Place.
     */
    public function listingHintSnapshots(): HasMany
    {
        return $this->hasMany(ListingHintSnapshot::class);
    }

    /**
     * Lists related Guest Hint Dismissal records for this Sleeping Place.
     */
    public function guestHintDismissals(): HasMany
    {
        return $this->hasMany(GuestHintDismissal::class);
    }

    /**
     * Lists related Guest Hint Impression records for this Sleeping Place.
     */
    public function guestHintImpressions(): HasMany
    {
        return $this->hasMany(GuestHintImpression::class);
    }

    /**
     * Lists related Host Hint Snapshot records for this Sleeping Place.
     */
    public function hostHintSnapshots(): HasMany
    {
        return $this->hasMany(HostHintSnapshot::class);
    }

    /**
     * Lists related Host Hint Dismissal records for this Sleeping Place.
     */
    public function hostHintDismissals(): HasMany
    {
        return $this->hasMany(HostHintDismissal::class);
    }

    /**
     * Lists related Host Calendar Event records for this Sleeping Place.
     */
    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class);
    }

    /**
     * Lists related Host Calendar Note records for this Sleeping Place.
     */
    public function hostCalendarNotes(): HasMany
    {
        return $this->hasMany(HostCalendarNote::class);
    }

    /**
     * Lists related Host Current Stay Snapshot records for this Sleeping Place.
     */
    public function hostCurrentStaySnapshots(): HasMany
    {
        return $this->hasMany(HostCurrentStaySnapshot::class);
    }

    /**
     * Lists related Host Guest Stay Note records for this Sleeping Place.
     */
    public function hostGuestStayNotes(): HasMany
    {
        return $this->hasMany(HostGuestStayNote::class);
    }

    /**
     * Connects this Sleeping Place to related Amenity records through a pivot relation.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'sleeping_place_amenity')->withTimestamps();
    }

    /**
     * Connects this Sleeping Place to related Rule records through a pivot relation.
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'sleeping_place_rule')->withTimestamps();
    }

    /**
     * Lists related Sleeping Place Photo records for this Sleeping Place.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(SleepingPlacePhoto::class);
    }

    /**
     * Lists related Media Item records attached to this Sleeping Place through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    /**
     * Fetches the single Media Item record attached to this Sleeping Place through a polymorphic relation.
     */
    public function cardMedia(): MorphOne
    {
        return $this->morphOne(MediaItem::class, 'mediable')
            ->active()
            ->orderByDesc('is_primary')
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Adds the active query filter for reusable Sleeping Place lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), SleepingPlaceStatus::Active);
    }

    /**
     * Adds the visible query filter for reusable Sleeping Place lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), SleepingPlaceStatus::Active);
    }

    /**
     * Adds the translated query filter for reusable Sleeping Place lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    /**
     * Adds the in city query filter for reusable Sleeping Place lookups.
     */
    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->whereHas('property', fn (Builder $property) => $property->where('city_id', $cityId));
    }

    /**
     * Adds the for host query filter for reusable Sleeping Place lookups.
     */
    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $builder) use ($userId): void {
            $builder
                ->where($builder->qualifyColumn('user_id'), $userId)
                ->orWhereHas('property', fn (Builder $property) => $property->where('host_user_id', $userId));
        });
    }

    /**
     * Adds the for room query filter for reusable Sleeping Place lookups.
     */
    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where($query->qualifyColumn('room_id'), $roomId);
    }

    /**
     * Adds the for property query filter for reusable Sleeping Place lookups.
     */
    public function scopeForProperty(Builder $query, int $propertyId): Builder
    {
        return $query->where($query->qualifyColumn('property_id'), $propertyId);
    }

    /**
     * Adds the for guest query filter for reusable Sleeping Place lookups.
     */
    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->whereHas('bookings', fn (Builder $booking) => $booking->where('guest_user_id', $userId));
    }

    /**
     * Adds the by type query filter for reusable Sleeping Place lookups.
     */
    public function scopeByType(Builder $query, SleepingPlaceType|string $type): Builder
    {
        $value = $type instanceof SleepingPlaceType ? $type->value : $type;

        return $query->where(function (Builder $builder) use ($value): void {
            $builder->where($builder->qualifyColumn('sleeping_place_type'), $value)
                ->orWhere($builder->qualifyColumn('type'), $value);
        });
    }

    /**
     * Adds the top bunk query filter for reusable Sleeping Place lookups.
     */
    public function scopeTopBunk(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('is_top_bunk'), true)
                ->orWhere($builder->qualifyColumn('bunk_level'), 'top')
                ->orWhere($builder->qualifyColumn('type'), SleepingPlaceType::BunkTop->value);
        });
    }

    /**
     * Adds the bottom bunk query filter for reusable Sleeping Place lookups.
     */
    public function scopeBottomBunk(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('is_bottom_bunk'), true)
                ->orWhere($builder->qualifyColumn('bunk_level'), 'bottom')
                ->orWhere($builder->qualifyColumn('type'), SleepingPlaceType::BunkBottom->value);
        });
    }

    /**
     * Adds the with locker query filter for reusable Sleeping Place lookups.
     */
    public function scopeWithLocker(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_locker'), true)
                ->orWhereHas('storageDetails', fn (Builder $details) => $details->where('has_personal_locker', true));
        });
    }

    /**
     * Adds the with curtain query filter for reusable Sleeping Place lookups.
     */
    public function scopeWithCurtain(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_curtain'), true)
                ->orWhereHas('positionDetails', fn (Builder $details) => $details->where('has_curtain', true));
        });
    }

    /**
     * Adds the with power socket query filter for reusable Sleeping Place lookups.
     */
    public function scopeWithPowerSocket(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_power_socket'), true)
                ->orWhereHas('positionDetails', fn (Builder $details) => $details->where('has_power_socket', true));
        });
    }

    /**
     * Adds the with bedding query filter for reusable Sleeping Place lookups.
     */
    public function scopeWithBedding(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_bedding'), true)
                ->orWhereHas('comfortDetails', fn (Builder $details) => $details->where('has_bedding', true));
        });
    }

    /**
     * Adds the with towel query filter for reusable Sleeping Place lookups.
     */
    public function scopeWithTowel(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('has_towel'), true)
                ->orWhereHas('comfortDetails', fn (Builder $details) => $details->where('has_towel', true));
        });
    }

    /**
     * Adds the suitable for tall person query filter for reusable Sleeping Place lookups.
     */
    public function scopeSuitableForTallPerson(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('suitable_for_tall_person'), true)
                ->orWhereHas('physicalDetails', fn (Builder $details) => $details->where('suitable_for_tall_person', true));
        });
    }

    /**
     * Adds the suitable for heavy person query filter for reusable Sleeping Place lookups.
     */
    public function scopeSuitableForHeavyPerson(Builder $query): Builder
    {
        return $query->whereHas('physicalDetails', fn (Builder $details) => $details->where('suitable_for_heavy_person', true));
    }

    /**
     * Adds the suitable for couple query filter for reusable Sleeping Place lookups.
     */
    public function scopeSuitableForCouple(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('is_for_couple'), true)
                ->orWhere($builder->qualifyColumn('max_guests'), '>=', 2);
        });
    }

    /**
     * Adds the instant booking query filter for reusable Sleeping Place lookups.
     */
    public function scopeInstantBooking(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('instant_booking_enabled'), true);
    }

    /**
     * Adds the available between query filter for reusable Sleeping Place lookups.
     */
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

    /**
     * Lists active and historical stay records tied to this Sleeping Place.
     */
    public function bookingStays(): HasMany
    {
        return $this->hasMany(BookingStay::class);
    }
}
