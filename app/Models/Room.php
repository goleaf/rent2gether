<?php

namespace App\Models;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'title',
        'gender_type',
        'description',
        'capacity',
        'area_sqm',
        'has_lock',
        'has_window',
        'has_wardrobe',
        'has_desk',
        'has_ac',
        'has_heating',
        'has_balcony',
        'rules',
        'status',
        'type',
        'is_private',
        'is_pass_through',
        'room_number',
        'floor',
        'area',
        'beds_count',
        'max_guests',
        'occupied_places_count',
        'available_places_count',
        'gender_policy',
        'min_guest_age',
        'max_guest_age',
        'windows_count',
        'window_view',
        'has_chair',
        'has_mirror',
        'has_air_conditioning',
        'has_curtains',
        'has_blackout_curtains',
        'noise_level',
        'light_level',
        'ventilation_level',
        'can_eat',
        'can_work_at_night',
        'can_turn_light_at_night',
        'can_talk_at_night',
        'room_rules_text',
    ];

    protected function casts(): array
    {
        return [
            'gender_type' => GenderType::class,
            'gender_policy' => GenderType::class,
            'type' => RoomType::class,
            'status' => RoomStatus::class,
            'is_private' => 'boolean',
            'is_pass_through' => 'boolean',
            'has_lock' => 'boolean',
            'has_window' => 'boolean',
            'windows_count' => 'integer',
            'has_wardrobe' => 'boolean',
            'has_desk' => 'boolean',
            'has_ac' => 'boolean',
            'has_heating' => 'boolean',
            'has_balcony' => 'boolean',
            'rules' => 'array',
            'area_sqm' => 'decimal:1',
            'area' => 'decimal:2',
            'has_chair' => 'boolean',
            'has_mirror' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_curtains' => 'boolean',
            'has_blackout_curtains' => 'boolean',
            'can_eat' => 'boolean',
            'can_work_at_night' => 'boolean',
            'can_turn_light_at_night' => 'boolean',
            'can_talk_at_night' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(RoomTranslation::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function sleepingPlaces(): HasMany
    {
        return $this->hasMany(SleepingPlace::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenity')->withTimestamps();
    }

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'room_rule')->withTimestamps();
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
        return $query->where('status', RoomStatus::Active->value);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Active->value);
    }

    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->whereHas('property', fn (Builder $property) => $property->where('city_id', $cityId));
    }

    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereHas('sleepingPlaces', fn (Builder $sleepingPlace) => $sleepingPlace->availableBetween($start, $end));
    }

    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->whereHas('property', fn (Builder $property) => $property->where('host_user_id', $userId));
    }

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->whereHas('sleepingPlaces.bookings', fn (Builder $booking) => $booking->where('guest_user_id', $userId));
    }

    public function scopeGender(Builder $query, GenderType $gender): Builder
    {
        return $query->where(function (Builder $builder) use ($gender): void {
            $builder->where('gender_policy', $gender->value)
                ->orWhere('gender_policy', GenderType::Mixed->value)
                ->orWhere('gender_policy', GenderType::NoRestriction->value);
        });
    }
}
