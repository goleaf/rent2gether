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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'title',
        'gender_type',
        'description',
        'capacity',
        'area_sqm',
        'has_lock',
        'has_lockable_door',
        'has_room_key',
        'has_window',
        'has_wardrobe',
        'has_lockers',
        'has_desk',
        'has_chairs',
        'has_ac',
        'has_heating',
        'has_fan',
        'has_balcony',
        'rules',
        'status',
        'type',
        'room_type',
        'living_format',
        'internal_name',
        'is_private',
        'is_shared',
        'is_pass_through',
        'is_for_one_person',
        'is_for_couples',
        'is_for_groups',
        'is_for_long_stay',
        'is_for_short_stay',
        'room_number',
        'floor',
        'area',
        'beds_count',
        'sleeping_places_count',
        'active_sleeping_places_count',
        'max_guests',
        'current_guests_count',
        'permanent_residents_count',
        'short_term_guests_count',
        'occupied_places_count',
        'available_places_count',
        'occupied_sleeping_places_count',
        'free_sleeping_places_count',
        'unavailable_sleeping_places_count',
        'gender_policy',
        'min_guest_age',
        'max_guest_age',
        'can_book_entire_room',
        'can_book_individual_places',
        'sort_order',
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
        'rules_text',
        'publication_status',
        'completed_at',
    ];

    /**
     * Defines how Laravel converts stored Room attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'gender_type' => GenderType::class,
            'gender_policy' => GenderType::class,
            'type' => RoomType::class,
            'room_type' => RoomType::class,
            'status' => RoomStatus::class,
            'is_private' => 'boolean',
            'is_shared' => 'boolean',
            'is_pass_through' => 'boolean',
            'is_for_one_person' => 'boolean',
            'is_for_couples' => 'boolean',
            'is_for_groups' => 'boolean',
            'is_for_long_stay' => 'boolean',
            'is_for_short_stay' => 'boolean',
            'has_lock' => 'boolean',
            'has_lockable_door' => 'boolean',
            'has_room_key' => 'boolean',
            'has_window' => 'boolean',
            'windows_count' => 'integer',
            'has_wardrobe' => 'boolean',
            'has_lockers' => 'boolean',
            'has_desk' => 'boolean',
            'has_chairs' => 'boolean',
            'has_ac' => 'boolean',
            'has_heating' => 'boolean',
            'has_fan' => 'boolean',
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
            'can_book_entire_room' => 'boolean',
            'can_book_individual_places' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Registers lifecycle hooks that keep Room records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (Room $room): void {
            if (! $room->user_id && $room->property_id) {
                $room->user_id = $room->relationLoaded('property')
                    ? $room->property?->host_user_id
                    : Property::query()->whereKey($room->property_id)->value('host_user_id');
            }

            $room->has_lockable_door = (bool) ($room->has_lockable_door || $room->has_lock);
            $room->has_chairs = (bool) ($room->has_chairs || $room->has_chair);
            $room->rules_text ??= $room->room_rules_text;
        });
    }

    /**
     * Links this Room to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Room to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lists related Room Translation records for this Room.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(RoomTranslation::class);
    }

    /**
     * Fetches the single Room Layout Detail record used by this Room.
     */
    public function layoutDetails(): HasOne
    {
        return $this->hasOne(RoomLayoutDetail::class);
    }

    /**
     * Fetches the single Room Comfort Detail record used by this Room.
     */
    public function comfortDetails(): HasOne
    {
        return $this->hasOne(RoomComfortDetail::class);
    }

    /**
     * Fetches the single Room Access Detail record used by this Room.
     */
    public function accessDetails(): HasOne
    {
        return $this->hasOne(RoomAccessDetail::class);
    }

    /**
     * Fetches the single Room Condition Detail record used by this Room.
     */
    public function conditionDetails(): HasOne
    {
        return $this->hasOne(RoomConditionDetail::class);
    }

    /**
     * Fetches the single Room Compatibility Profile record used by this Room.
     */
    public function compatibilityProfile(): HasOne
    {
        return $this->hasOne(RoomCompatibilityProfile::class);
    }

    /**
     * Lists related Bed records for this Room.
     */
    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    /**
     * Lists related Sleeping Place records for this Room.
     */
    public function sleepingPlaces(): HasMany
    {
        return $this->hasMany(SleepingPlace::class);
    }

    /**
     * Lists listing mismatch reports tied to this room.
     */
    public function listingMismatchReports(): HasMany
    {
        return $this->hasMany(BookingListingMismatchReport::class);
    }

    /**
     * Lists related Booking Guest Intake records for this Room.
     */
    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    /**
     * Lists related Room Occupant Snapshot records for this Room.
     */
    public function occupantSnapshots(): HasMany
    {
        return $this->hasMany(RoomOccupantSnapshot::class);
    }

    /**
     * Lists related Listing Publication Check records for this Room.
     */
    public function publicationChecks(): HasMany
    {
        return $this->hasMany(ListingPublicationCheck::class);
    }

    /**
     * Lists related Listing Readiness Check records for this Room.
     */
    public function readinessChecks(): HasMany
    {
        return $this->hasMany(ListingReadinessCheck::class);
    }

    /**
     * Lists related Host Calendar Event records for this Room.
     */
    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class);
    }

    /**
     * Lists related Host Calendar Note records for this Room.
     */
    public function hostCalendarNotes(): HasMany
    {
        return $this->hasMany(HostCalendarNote::class);
    }

    /**
     * Lists related Host Current Stay Snapshot records for this Room.
     */
    public function hostCurrentStaySnapshots(): HasMany
    {
        return $this->hasMany(HostCurrentStaySnapshot::class);
    }

    /**
     * Lists related Host Guest Stay Note records for this Room.
     */
    public function hostGuestStayNotes(): HasMany
    {
        return $this->hasMany(HostGuestStayNote::class);
    }

    /**
     * Connects this Room to related Amenity records through a pivot relation.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenity')->withTimestamps();
    }

    /**
     * Connects this Room to related Rule records through a pivot relation.
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'room_rule')->withTimestamps();
    }

    /**
     * Lists related Room Photo records for this Room.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(RoomPhoto::class);
    }

    /**
     * Lists related Media Item records attached to this Room through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    /**
     * Lists related Host Hint Snapshot records for this Room.
     */
    public function hostHintSnapshots(): HasMany
    {
        return $this->hasMany(HostHintSnapshot::class);
    }

    /**
     * Lists related Host Hint Dismissal records for this Room.
     */
    public function hostHintDismissals(): HasMany
    {
        return $this->hasMany(HostHintDismissal::class);
    }

    /**
     * Fetches the single Media Item record attached to this Room through a polymorphic relation.
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
     * Adds the active query filter for reusable Room lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Active->value);
    }

    /**
     * Adds the visible query filter for reusable Room lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Active->value);
    }

    /**
     * Adds the translated query filter for reusable Room lookups.
     */
    public function scopeTranslated(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    /**
     * Adds the in city query filter for reusable Room lookups.
     */
    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->whereHas('property', fn (Builder $property) => $property->where('city_id', $cityId));
    }

    /**
     * Adds the available between query filter for reusable Room lookups.
     */
    public function scopeAvailableBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereHas('sleepingPlaces', fn (Builder $sleepingPlace) => $sleepingPlace->availableBetween($start, $end));
    }

    /**
     * Adds the for host query filter for reusable Room lookups.
     */
    public function scopeForHost(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $builder) use ($userId): void {
            $builder
                ->where('user_id', $userId)
                ->orWhereHas('property', fn (Builder $property) => $property->where('host_user_id', $userId));
        });
    }

    /**
     * Adds the for property query filter for reusable Room lookups.
     */
    public function scopeForProperty(Builder $query, int $propertyId): Builder
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Adds the for guest query filter for reusable Room lookups.
     */
    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->whereHas('sleepingPlaces.bookings', fn (Builder $booking) => $booking->where('guest_user_id', $userId));
    }

    /**
     * Adds the gender query filter for reusable Room lookups.
     */
    public function scopeGender(Builder $query, GenderType $gender): Builder
    {
        return $query->where(function (Builder $builder) use ($gender): void {
            $builder->where('gender_policy', $gender->value)
                ->orWhere('gender_policy', GenderType::Mixed->value)
                ->orWhere('gender_policy', GenderType::NoRestriction->value);
        });
    }

    /**
     * Adds the with free places query filter for reusable Room lookups.
     */
    public function scopeWithFreePlaces(Builder $query): Builder
    {
        return $query->where('free_sleeping_places_count', '>', 0);
    }

    /**
     * Adds the by gender policy query filter for reusable Room lookups.
     */
    public function scopeByGenderPolicy(Builder $query, GenderType|string $gender): Builder
    {
        $value = $gender instanceof GenderType ? $gender->value : $gender;

        return $query->where('gender_policy', $value);
    }

    /**
     * Adds the shared query filter for reusable Room lookups.
     */
    public function scopeShared(Builder $query): Builder
    {
        return $query->where('is_shared', true);
    }

    /**
     * Adds the private query filter for reusable Room lookups.
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_private', true);
    }

    /**
     * Adds the quiet query filter for reusable Room lookups.
     */
    public function scopeQuiet(Builder $query): Builder
    {
        return $query->whereHas('comfortDetails', fn (Builder $comfort) => $comfort->where('noise_level', 'quiet'));
    }

    /**
     * Adds the with lock query filter for reusable Room lookups.
     */
    public function scopeWithLock(Builder $query): Builder
    {
        return $query->whereHas('accessDetails', fn (Builder $access) => $access->where('has_lock', true));
    }

    /**
     * Adds the with desk query filter for reusable Room lookups.
     */
    public function scopeWithDesk(Builder $query): Builder
    {
        return $query->whereHas('accessDetails', fn (Builder $access) => $access->where('has_desk', true));
    }

    /**
     * Adds the with personal lockers query filter for reusable Room lookups.
     */
    public function scopeWithPersonalLockers(Builder $query): Builder
    {
        return $query->whereHas('accessDetails', fn (Builder $access) => $access->where('has_personal_lockers', true));
    }

    /**
     * Adds the suitable for long stay query filter for reusable Room lookups.
     */
    public function scopeSuitableForLongStay(Builder $query): Builder
    {
        return $query->where('is_for_long_stay', true);
    }

    /**
     * Lists active and historical stay records tied to this Room.
     */
    public function bookingStays(): HasMany
    {
        return $this->hasMany(BookingStay::class);
    }

    /**
     * Fetches the current occupancy snapshot for this Room.
     */
    public function currentOccupancySnapshot(): HasOne
    {
        return $this->hasOne(RoomCurrentOccupancySnapshot::class);
    }
}
