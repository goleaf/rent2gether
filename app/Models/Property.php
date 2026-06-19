<?php

namespace App\Models;

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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'host_user_id',
        'country_id',
        'region_id',
        'city_id',
        'title',
        'type',
        'description',
        'country',
        'city',
        'district',
        'street',
        'building',
        'apartment',
        'floor',
        'has_elevator',
        'lat',
        'lng',
        'show_exact_address',
        'nearest_transport',
        'access_instructions',
        'amenities',
        'rules',
        'status',
        'address_line_1',
        'address_line_2',
        'house_number',
        'apartment_number',
        'total_floors',
        'latitude',
        'longitude',
        'approximate_latitude',
        'approximate_longitude',
        'show_exact_address_before_booking',
        'show_exact_address_after_payment',
        'distance_to_center_meters',
        'total_area',
        'rooms_count',
        'bathrooms_count',
        'showers_count',
        'kitchens_count',
        'balconies_count',
        'max_guests',
        'current_guests_count',
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
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'has_elevator' => 'boolean',
            'show_exact_address' => 'boolean',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'amenities' => 'array',
            'rules' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'approximate_latitude' => 'decimal:7',
            'approximate_longitude' => 'decimal:7',
            'show_exact_address_before_booking' => 'boolean',
            'show_exact_address_after_payment' => 'boolean',
            'total_area' => 'decimal:2',
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

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function sleepingPlaces(): HasMany
    {
        return $this->hasMany(SleepingPlace::class);
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

    public function isOwnedBy(User $user): bool
    {
        return (int) $this->host_user_id === (int) $user->id
            || (int) $this->user_id === (int) $user->id;
    }
}
