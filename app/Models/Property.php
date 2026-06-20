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
        'street',
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
        'current_residents_count',
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
    ];

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
            'can_book_whole_property' => 'boolean',
            'can_book_private_room' => 'boolean',
            'can_book_sleeping_place' => 'boolean',
            'has_heating' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_hot_water' => 'boolean',
            'has_parking' => 'boolean',
            'has_security' => 'boolean',
            'has_cctv_common_areas' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property): void {
            $property->host_user_id ??= $property->user_id;
            $property->user_id ??= $property->host_user_id;
        });

        static::deleting(function (Property $property): void {
            $property->hostHintSnapshots()->delete();
            $property->hostHintDismissals()->delete();
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function legacyHost(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function countryModel(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function cityModel(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PropertyTranslation::class);
    }

    public function locationDetails(): HasOne
    {
        return $this->hasOne(PropertyLocationDetail::class);
    }

    public function conditionDetails(): HasOne
    {
        return $this->hasOne(PropertyConditionDetail::class);
    }

    public function accessDetails(): HasOne
    {
        return $this->hasOne(PropertyAccessDetail::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function sleepingPlaces(): HasMany
    {
        return $this->hasMany(SleepingPlace::class);
    }

    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    public function beds(): HasManyThrough
    {
        return $this->hasManyThrough(Bed::class, Room::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity')->withTimestamps();
    }

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'property_rule')->withTimestamps();
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    public function hostHintSnapshots(): HasMany
    {
        return $this->hasMany(HostHintSnapshot::class);
    }

    public function hostHintDismissals(): HasMany
    {
        return $this->hasMany(HostHintDismissal::class);
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
        return $query->where('status', PropertyStatus::Active->value);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Active->value);
    }

    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->where('host_user_id', $userId);
    }

    public function scopeInDistrict(Builder $query, string $district): Builder
    {
        return $query->where('district', $district);
    }

    public function scopeWithElevator(Builder $query): Builder
    {
        return $query->where('has_elevator', true);
    }

    public function scopeWithFreePlaces(Builder $query): Builder
    {
        return $query->where('free_sleeping_places_count', '>', 0);
    }

    public function scopeSelfCheckIn(Builder $query): Builder
    {
        return $query->whereHas('accessDetails', fn (Builder $details): Builder => $details->where('self_check_in_available', true));
    }

    public function scopeWithParking(Builder $query): Builder
    {
        return $query->whereHas('locationDetails', fn (Builder $details): Builder => $details
            ->where('has_parking_nearby', true)
            ->orWhere('has_free_parking', true)
            ->orWhere('has_paid_parking', true));
    }

    public function scopeSafeDistrict(Builder $query): Builder
    {
        return $query->whereHas('locationDetails', fn (Builder $details): Builder => $details->whereIn('district_safety_level', ['good', 'high']));
    }

    public function scopeQuietDistrict(Builder $query): Builder
    {
        return $query->whereHas('locationDetails', fn (Builder $details): Builder => $details->whereIn('district_noise_level', ['quiet', 'low']));
    }

    public function scopeGoodCondition(Builder $query): Builder
    {
        return $query->whereHas('conditionDetails', fn (Builder $details): Builder => $details
            ->whereIn('repair_state', ['good', 'high', 'new'])
            ->whereIn('cleanliness_level', ['good', 'high']));
    }

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->whereHas('sleepingPlaces.bookings', fn (Builder $booking) => $booking->where('guest_user_id', $userId));
    }

    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereHas('sleepingPlaces', fn (Builder $sleepingPlace) => $sleepingPlace->availableBetween($start, $end));
    }

    public function isOwnedBy(User $user): bool
    {
        return (int) $this->host_user_id === (int) $user->id
            || (int) $this->user_id === (int) $user->id;
    }
}
