<?php

namespace App\Models;

use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'host_user_id',
        'rental_unit_type',
        'country_id',
        'region_id',
        'region_name',
        'city_id',
        'title',
        'type',
        'property_type',
        'property_subtype',
        'description',
        'country',
        'city',
        'district',
        'district_id',
        'street',
        'street_name',
        'building',
        'entrance',
        'apartment',
        'floor',
        'has_elevator',
        'lat',
        'lng',
        'show_exact_address',
        'nearest_transport',
        'distance_to_transport_meters',
        'access_instructions',
        'amenities',
        'rules',
        'status',
        'address_line_1',
        'address_line_2',
        'house_number',
        'apartment_number',
        'postal_code',
        'total_floors',
        'floors_count',
        'latitude',
        'longitude',
        'approximate_latitude',
        'approximate_longitude',
        'show_exact_address_before_booking',
        'show_exact_address_after_confirmation',
        'show_exact_address_after_payment',
        'show_only_approximate_location',
        'distance_to_center_meters',
        'total_area',
        'living_area',
        'rooms_count',
        'bedrooms_count',
        'shared_rooms_count',
        'pass_through_rooms_count',
        'bathrooms_count',
        'showers_count',
        'kitchens_count',
        'balconies_count',
        'max_guests',
        'current_guests_count',
        'max_residents',
        'max_residents_count',
        'current_residents_count',
        'free_places_count',
        'occupied_places_count',
        'permanent_residents_count',
        'short_term_guests_count',
        'active_rooms_count',
        'active_sleeping_places_count',
        'free_sleeping_places_count',
        'occupied_sleeping_places_count',
        'unavailable_sleeping_places_count',
        'can_book_whole_property',
        'can_book_private_room',
        'can_book_sleeping_place',
        'noise_level',
        'cleanliness_level',
        'safety_level',
        'repair_state',
        'has_heating',
        'has_air_conditioning',
        'has_hot_water',
        'has_parking',
        'has_security',
        'has_cctv_common_areas',
        'emergency_contact_name',
        'emergency_contact_phone',
        'publication_status',
        'review_status',
        'review_requested_at',
        'reviewed_at',
        'review_comment',
        'rejection_reason',
        'published_at',
        'paused_at',
        'archived_at',
    ];

    /**
     * Defines how Laravel converts stored Property attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'rental_unit_type' => PropertyRentalUnitType::class,
            'type' => PropertyType::class,
            'property_type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'has_elevator' => 'boolean',
            'show_exact_address' => 'boolean',
            'distance_to_transport_meters' => 'integer',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'amenities' => 'array',
            'rules' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'approximate_latitude' => 'decimal:7',
            'approximate_longitude' => 'decimal:7',
            'show_exact_address_before_booking' => 'boolean',
            'show_exact_address_after_confirmation' => 'boolean',
            'show_exact_address_after_payment' => 'boolean',
            'show_only_approximate_location' => 'boolean',
            'total_area' => 'decimal:2',
            'living_area' => 'decimal:2',
            'floors_count' => 'integer',
            'max_residents_count' => 'integer',
            'free_places_count' => 'integer',
            'occupied_places_count' => 'integer',
            'can_book_whole_property' => 'boolean',
            'can_book_private_room' => 'boolean',
            'can_book_sleeping_place' => 'boolean',
            'has_heating' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_hot_water' => 'boolean',
            'has_parking' => 'boolean',
            'has_security' => 'boolean',
            'has_cctv_common_areas' => 'boolean',
            'review_requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'paused_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Registers lifecycle hooks that keep Property records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (Property $property): void {
            $property->host_user_id ??= $property->user_id;
            $property->user_id ??= $property->host_user_id;
            $property->street_name ??= $property->street;
            $property->floors_count ??= $property->total_floors;
            $property->max_residents_count ??= $property->max_residents;
            $property->free_places_count ??= $property->free_sleeping_places_count ?? 0;
            $property->occupied_places_count ??= $property->occupied_sleeping_places_count ?? 0;
        });

        static::deleting(function (Property $property): void {
            $property->hostHintSnapshots()->delete();
            $property->hostHintDismissals()->delete();
            $property->listingWizardSessions()->delete();
            $property->publicationChecks()->delete();
        });
    }

    /**
     * Links this Property to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Property to the User record used by its legacy host relation.
     */
    public function legacyHost(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Property to the Country record used by its country model relation.
     */
    public function countryModel(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Links this Property to the Country record used by its country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Links this Property to the Region record used by its region relation.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Links this Property to the City record used by its city model relation.
     */
    public function cityModel(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Links this Property to the City record used by its city relation.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Lists related Property Translation records for this Property.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PropertyTranslation::class);
    }

    /**
     * Fetches the single Property Location Detail record used by this Property.
     */
    public function locationDetails(): HasOne
    {
        return $this->hasOne(PropertyLocationDetail::class);
    }

    /**
     * Fetches the single Property Condition Detail record used by this Property.
     */
    public function conditionDetails(): HasOne
    {
        return $this->hasOne(PropertyConditionDetail::class);
    }

    /**
     * Fetches the single Property Access Detail record used by this Property.
     */
    public function accessDetails(): HasOne
    {
        return $this->hasOne(PropertyAccessDetail::class);
    }

    /**
     * Fetches the single Property Address record used by this Property.
     */
    public function address(): HasOne
    {
        return $this->hasOne(PropertyAddress::class);
    }

    /**
     * Lists related Room records for this Property.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Lists related Sleeping Place records for this Property.
     */
    public function sleepingPlaces(): HasMany
    {
        return $this->hasMany(SleepingPlace::class);
    }

    /**
     * Lists listing mismatch reports tied to this property.
     */
    public function listingMismatchReports(): HasMany
    {
        return $this->hasMany(BookingListingMismatchReport::class);
    }

    /**
     * Lists related Booking Guest Intake records for this Property.
     */
    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    /**
     * Lists related Host Listing Wizard Session records for this Property.
     */
    public function listingWizardSessions(): HasMany
    {
        return $this->hasMany(HostListingWizardSession::class);
    }

    /**
     * Lists related Listing Publication Check records for this Property.
     */
    public function publicationChecks(): HasMany
    {
        return $this->hasMany(ListingPublicationCheck::class);
    }

    /**
     * Lists related Listing Readiness Check records for this Property.
     */
    public function readinessChecks(): HasMany
    {
        return $this->hasMany(ListingReadinessCheck::class);
    }

    /**
     * Lists related Listing Creation Draft records for this Property.
     */
    public function listingCreationDrafts(): HasMany
    {
        return $this->hasMany(ListingCreationDraft::class);
    }

    /**
     * Lists related Host Calendar Event records for this Property.
     */
    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class);
    }

    /**
     * Lists related Host Calendar Note records for this Property.
     */
    public function hostCalendarNotes(): HasMany
    {
        return $this->hasMany(HostCalendarNote::class);
    }

    /**
     * Lists related Host Current Stay Snapshot records for this Property.
     */
    public function hostCurrentStaySnapshots(): HasMany
    {
        return $this->hasMany(HostCurrentStaySnapshot::class);
    }

    /**
     * Lists related Host Guest Stay Note records for this Property.
     */
    public function hostGuestStayNotes(): HasMany
    {
        return $this->hasMany(HostGuestStayNote::class);
    }

    /**
     * Lists related Bed records reached through an intermediate model from this Property.
     */
    public function beds(): HasManyThrough
    {
        return $this->hasManyThrough(Bed::class, Room::class);
    }

    /**
     * Connects this Property to related Amenity records through a pivot relation.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity')->withTimestamps();
    }

    /**
     * Lists related Property Amenity records for this Property.
     */
    public function amenityRecords(): HasMany
    {
        return $this->hasMany(PropertyAmenity::class);
    }

    /**
     * Connects this Property to related Rule records through a pivot relation.
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'property_rule')->withTimestamps();
    }

    /**
     * Lists related Property Rule records for this Property.
     */
    public function ruleRecords(): HasMany
    {
        return $this->hasMany(PropertyRule::class);
    }

    /**
     * Lists related Property Photo records for this Property.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class);
    }

    /**
     * Lists related Media Item records attached to this Property through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    /**
     * Lists related Host Hint Snapshot records for this Property.
     */
    public function hostHintSnapshots(): HasMany
    {
        return $this->hasMany(HostHintSnapshot::class);
    }

    /**
     * Lists related Host Hint Dismissal records for this Property.
     */
    public function hostHintDismissals(): HasMany
    {
        return $this->hasMany(HostHintDismissal::class);
    }

    /**
     * Fetches the single Media Item record attached to this Property through a polymorphic relation.
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
     * Adds the active query filter for reusable Property lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Active->value);
    }

    /**
     * Adds the visible query filter for reusable Property lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Active->value);
    }

    /**
     * Adds the translated query filter for reusable Property lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    /**
     * Adds the in city query filter for reusable Property lookups.
     */
    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Adds the for host query filter for reusable Property lookups.
     */
    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->where('host_user_id', $userId);
    }

    /**
     * Adds the in district query filter for reusable Property lookups.
     */
    public function scopeInDistrict(Builder $query, string $district): Builder
    {
        return $query->where('district', $district);
    }

    /**
     * Adds the with elevator query filter for reusable Property lookups.
     */
    public function scopeWithElevator(Builder $query): Builder
    {
        return $query->where('has_elevator', true);
    }

    /**
     * Adds the with free places query filter for reusable Property lookups.
     */
    public function scopeWithFreePlaces(Builder $query): Builder
    {
        return $query->where('free_sleeping_places_count', '>', 0);
    }

    /**
     * Adds the self check in query filter for reusable Property lookups.
     */
    public function scopeSelfCheckIn(Builder $query): Builder
    {
        return $query->whereHas('accessDetails', fn (Builder $details): Builder => $details->where('self_check_in_available', true));
    }

    /**
     * Adds the with parking query filter for reusable Property lookups.
     */
    public function scopeWithParking(Builder $query): Builder
    {
        return $query->whereHas('locationDetails', fn (Builder $details): Builder => $details
            ->where('has_parking_nearby', true)
            ->orWhere('has_free_parking', true)
            ->orWhere('has_paid_parking', true));
    }

    /**
     * Adds the safe district query filter for reusable Property lookups.
     */
    public function scopeSafeDistrict(Builder $query): Builder
    {
        return $query->whereHas('locationDetails', fn (Builder $details): Builder => $details->whereIn('district_safety_level', ['good', 'high']));
    }

    /**
     * Adds the quiet district query filter for reusable Property lookups.
     */
    public function scopeQuietDistrict(Builder $query): Builder
    {
        return $query->whereHas('locationDetails', fn (Builder $details): Builder => $details->whereIn('district_noise_level', ['quiet', 'low']));
    }

    /**
     * Adds the good condition query filter for reusable Property lookups.
     */
    public function scopeGoodCondition(Builder $query): Builder
    {
        return $query->whereHas('conditionDetails', fn (Builder $details): Builder => $details
            ->whereIn('repair_state', ['good', 'high', 'new'])
            ->whereIn('cleanliness_level', ['good', 'high']));
    }

    /**
     * Adds the for guest query filter for reusable Property lookups.
     */
    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->whereHas('sleepingPlaces.bookings', fn (Builder $booking) => $booking->where('guest_user_id', $userId));
    }

    /**
     * Adds the available between query filter for reusable Property lookups.
     */
    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereHas('sleepingPlaces', fn (Builder $sleepingPlace) => $sleepingPlace->availableBetween($start, $end));
    }

    /**
     * Checks whether the given User owns this Property.
     */
    public function isOwnedBy(User $user): bool
    {
        return (int) $this->host_user_id === (int) $user->id
            || (int) $this->user_id === (int) $user->id;
    }

    /**
     * Lists active and historical stay records tied to this Property.
     */
    public function bookingStays(): HasMany
    {
        return $this->hasMany(BookingStay::class);
    }

    /**
     * Lists host-unresponsive policy fallbacks configured for this Property.
     */
    public function hostUnresponsivePolicies(): HasMany
    {
        return $this->hasMany(HostUnresponsivePolicy::class);
    }

    /**
     * Fetches the active host-unresponsive policy fallback for new Booking snapshots.
     */
    public function activeHostUnresponsivePolicy(): HasOne
    {
        return $this->hasOne(HostUnresponsivePolicy::class)->where('active', true);
    }

    /**
     * Fetches the current occupancy snapshot for this Property.
     */
    public function currentOccupancySnapshot(): HasOne
    {
        return $this->hasOne(PropertyCurrentOccupancySnapshot::class);
    }
}
