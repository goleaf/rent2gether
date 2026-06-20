<?php

namespace App\Models;

use App\Enums\UserRoleMode;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_verified',
        'phone_verified_at',
        'avatar',
        'avatar_path',
        'role_mode',
        'preferred_locale',
        'timezone',
        'is_guest',
        'date_of_birth',
        'gender',
        'country',
        'city',
        'languages',
        'bio',
        'occupation',
        'travel_purpose',
        'is_smoker',
        'has_pets',
        'has_allergies',
        'prefers_quiet',
        'sleep_schedule',
        'willing_to_share_room',
        'preferred_room_gender',
        'identity_verified',
        'identity_verified_at',
        'is_host',
        'host_description',
        'host_experience_years',
        'host_experience_started_year',
        'host_lives_on_site',
        'preferred_contact_method',
        'rating_as_guest',
        'rating_as_host',
        'completed_stays_count',
        'hosted_stays_count',
        'cancellations_count',
        'complaints_count',
        'status',
        'last_active_at',
        'last_seen_at',
        'last_login_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Defines how Laravel converts stored User attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified' => 'boolean',
            'phone_verified_at' => 'datetime',
            'role_mode' => UserRoleMode::class,
            'is_guest' => 'boolean',
            'date_of_birth' => 'date',
            'languages' => 'array',
            'is_smoker' => 'boolean',
            'has_pets' => 'boolean',
            'has_allergies' => 'boolean',
            'prefers_quiet' => 'boolean',
            'willing_to_share_room' => 'boolean',
            'identity_verified' => 'boolean',
            'identity_verified_at' => 'datetime',
            'is_host' => 'boolean',
            'host_experience_years' => 'integer',
            'host_experience_started_year' => 'integer',
            'host_lives_on_site' => 'boolean',
            'rating_as_guest' => 'decimal:2',
            'rating_as_host' => 'decimal:2',
            'status' => UserStatus::class,
            'last_active_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Fetches the single User Profile record used by this User.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Fetches the single Guest Preference record used by this User.
     */
    public function guestPreference(): HasOne
    {
        return $this->hasOne(GuestPreference::class);
    }

    /**
     * Fetches the single Guest Profile record used by this User.
     */
    public function guestProfile(): HasOne
    {
        return $this->hasOne(GuestProfile::class);
    }

    /**
     * Fetches the single Guest Compatibility Profile record used by this User.
     */
    public function guestCompatibilityProfile(): HasOne
    {
        return $this->hasOne(GuestCompatibilityProfile::class);
    }

    /**
     * Fetches the single Guest Compatibility Visibility Setting record used by this User.
     */
    public function guestCompatibilityVisibilitySetting(): HasOne
    {
        return $this->hasOne(GuestCompatibilityVisibilitySetting::class);
    }

    /**
     * Fetches the single Host Profile record used by this User.
     */
    public function hostProfile(): HasOne
    {
        return $this->hasOne(HostProfile::class);
    }

    /**
     * Lists related Host Representative records for this User.
     */
    public function hostRepresentatives(): HasMany
    {
        return $this->hasMany(HostRepresentative::class, 'host_user_id');
    }

    /**
     * Lists related Host Representative records for this User.
     */
    public function representativeContacts(): HasMany
    {
        return $this->hasMany(HostRepresentative::class, 'representative_user_id');
    }

    /**
     * Lists related User Verification records for this User.
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(UserVerification::class);
    }

    /**
     * Lists related User Document records for this User.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }

    /**
     * Lists related User Language records for this User.
     */
    public function userLanguages(): HasMany
    {
        return $this->hasMany(UserLanguage::class);
    }

    /**
     * Lists related User Language records for this User.
     */
    public function languages(): HasMany
    {
        return $this->hasMany(UserLanguage::class);
    }

    /**
     * Fetches the single User Privacy Setting record used by this User.
     */
    public function privacySetting(): HasOne
    {
        return $this->hasOne(UserPrivacySetting::class);
    }

    /**
     * Fetches the single User Saved Preference record used by this User.
     */
    public function savedPreference(): HasOne
    {
        return $this->hasOne(UserSavedPreference::class);
    }

    /**
     * Fetches the single User Activity Summary record used by this User.
     */
    public function activitySummary(): HasOne
    {
        return $this->hasOne(UserActivitySummary::class);
    }

    /**
     * Lists related User Notification Preference records for this User.
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /**
     * Lists related Property records for this User.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'host_user_id');
    }

    /**
     * Lists related Room records for this User.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Lists related Sleeping Place records for this User.
     */
    public function sleepingPlaces(): HasMany
    {
        return $this->hasMany(SleepingPlace::class);
    }

    /**
     * Lists related Property records for this User.
     */
    public function legacyProperties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Lists related Payout records for this User.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'host_id');
    }

    /**
     * Lists related Booking records for this User.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'guest_user_id');
    }

    /**
     * Lists related Booking Guest Intake records for this User.
     */
    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    /**
     * Lists related Booking records for this User.
     */
    public function hostedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'host_user_id');
    }

    /**
     * Lists related Favorite records for this User.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Lists related Favorite Collection records for this User.
     */
    public function favoriteCollections(): HasMany
    {
        return $this->hasMany(FavoriteCollection::class);
    }

    /**
     * Lists related Saved Search records for this User.
     */
    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    /**
     * Fetches the single User Setting record used by this User.
     */
    public function setting(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Lists related Waitlist Entry records for this User.
     */
    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    /**
     * Lists related Waitlist Item records for this User.
     */
    public function waitlistItems(): HasMany
    {
        return $this->hasMany(WaitlistItem::class);
    }

    /**
     * Fetches the single Co Living Profile record used by this User.
     */
    public function coLivingProfile(): HasOne
    {
        return $this->hasOne(CoLivingProfile::class);
    }

    /**
     * Fetches the single Co Living Visibility Setting record used by this User.
     */
    public function coLivingVisibilitySetting(): HasOne
    {
        return $this->hasOne(CoLivingVisibilitySetting::class);
    }

    /**
     * Lists related Room Occupant Snapshot records for this User.
     */
    public function roomOccupantSnapshots(): HasMany
    {
        return $this->hasMany(RoomOccupantSnapshot::class);
    }

    /**
     * Lists related Host Calendar Event records for this User.
     */
    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class);
    }

    /**
     * Lists related Host Calendar Note records for this User.
     */
    public function hostCalendarNotes(): HasMany
    {
        return $this->hasMany(HostCalendarNote::class);
    }

    /**
     * Fetches the single Host Calendar View Setting record used by this User.
     */
    public function hostCalendarViewSetting(): HasOne
    {
        return $this->hasOne(HostCalendarViewSetting::class);
    }

    /**
     * Lists related Host Current Stay Snapshot records for this User.
     */
    public function hostedCurrentStaySnapshots(): HasMany
    {
        return $this->hasMany(HostCurrentStaySnapshot::class, 'user_id');
    }

    /**
     * Lists related Host Current Stay Snapshot records for this User.
     */
    public function guestCurrentStaySnapshots(): HasMany
    {
        return $this->hasMany(HostCurrentStaySnapshot::class, 'guest_user_id');
    }

    /**
     * Lists related Host Guest Stay Note records for this User.
     */
    public function hostGuestStayNotes(): HasMany
    {
        return $this->hasMany(HostGuestStayNote::class, 'user_id');
    }

    /**
     * Lists related Host Guest Stay Note records for this User.
     */
    public function guestStayNotes(): HasMany
    {
        return $this->hasMany(HostGuestStayNote::class, 'guest_user_id');
    }

    /**
     * Lists related Host Guest Stay Flag records for this User.
     */
    public function hostGuestStayFlags(): HasMany
    {
        return $this->hasMany(HostGuestStayFlag::class, 'user_id');
    }

    /**
     * Lists related Host Guest Stay Flag records for this User.
     */
    public function guestStayFlags(): HasMany
    {
        return $this->hasMany(HostGuestStayFlag::class, 'guest_user_id');
    }

    /**
     * Lists related Compatibility Result records for this User.
     */
    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(CompatibilityResult::class);
    }

    /**
     * Lists related Review records for this User.
     */
    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Lists related Review records for this User.
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * Lists related Conversation records for this User.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'participant_one_id');
    }

    /**
     * Lists related Message Thread records for this User.
     */
    public function guestMessageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class, 'guest_user_id');
    }

    /**
     * Lists related Message Thread records for this User.
     */
    public function hostMessageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class, 'host_user_id');
    }

    /**
     * Lists related Complaint records for this User.
     */
    public function complaintsReported(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }

    /**
     * Lists related Complaint records for this User.
     */
    public function complaintsAgainst(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reported_user_id');
    }

    /**
     * Lists related Notification records for this User.
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Lists related Host Hint Snapshot records for this User.
     */
    public function hostHintSnapshots(): HasMany
    {
        return $this->hasMany(HostHintSnapshot::class);
    }

    /**
     * Lists related Host Hint Dismissal records for this User.
     */
    public function hostHintDismissals(): HasMany
    {
        return $this->hasMany(HostHintDismissal::class);
    }

    /**
     * Lists related Host Hint Action records for this User.
     */
    public function hostHintActions(): HasMany
    {
        return $this->hasMany(HostHintAction::class);
    }

    /**
     * Lists related Host Listing Wizard Session records for this User.
     */
    public function hostListingWizardSessions(): HasMany
    {
        return $this->hasMany(HostListingWizardSession::class);
    }

    /**
     * Lists related Listing Publication Check records for this User.
     */
    public function listingPublicationChecks(): HasMany
    {
        return $this->hasMany(ListingPublicationCheck::class);
    }

    /**
     * Lists related Media Item records attached to this User through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    /**
     * Fetches the single Media Item record attached to this User through a polymorphic relation.
     */
    public function avatarMedia(): MorphOne
    {
        return $this->morphOne(MediaItem::class, 'mediable')
            ->active()
            ->where('collection', 'avatar')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Adds the hosts query filter for reusable User lookups.
     */
    public function scopeHosts(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder
                ->where('is_host', true)
                ->orWhereIn('role_mode', [UserRoleMode::Host->value, UserRoleMode::GuestHost->value]);
        });
    }

    /**
     * Adds the verified query filter for reusable User lookups.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('identity_verified', true);
    }

    /**
     * Adds the active query filter for reusable User lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active->value);
    }

    /**
     * Checks whether this User can act as a host.
     */
    public function isHost(): bool
    {
        $roleMode = $this->role_mode instanceof UserRoleMode
            ? $this->role_mode
            : UserRoleMode::tryFrom((string) $this->role_mode);

        return (bool) $this->is_host
            || $roleMode?->canHost() === true
            || $this->hostProfile()->exists();
    }

    /**
     * Checks whether this User can act as a guest.
     */
    public function isGuest(): bool
    {
        $roleMode = $this->role_mode instanceof UserRoleMode
            ? $this->role_mode
            : UserRoleMode::tryFrom((string) $this->role_mode);

        return (bool) $this->is_guest || $roleMode?->canGuest() === true;
    }

    /**
     * Checks whether this User can act as both guest and host.
     */
    public function isGuestHost(): bool
    {
        return ($this->role_mode instanceof UserRoleMode ? $this->role_mode : UserRoleMode::tryFrom((string) $this->role_mode))
            === UserRoleMode::GuestHost;
    }

    /**
     * Returns this User age when a birth date is available.
     */
    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Returns the profile completion percentage for this User.
     */
    public function profileCompleteness(): int
    {
        $fields = ['name', 'avatar', 'phone', 'date_of_birth', 'country', 'city', 'languages', 'bio'];
        $filled = collect($fields)->filter(fn (string $field) => ! empty($this->{$field}))->count();

        return (int) round(($filled / count($fields)) * 100);
    }

    /**
     * Returns the trust level text for this User.
     */
    public function trustLevel(): string
    {
        if ($this->identity_verified && $this->completed_stays_count >= 3 && $this->complaints_count === 0) {
            return 'trusted';
        }

        if ($this->identity_verified) {
            return 'verified';
        }

        if ($this->phone_verified && $this->email_verified_at) {
            return 'confirmed';
        }

        return 'new';
    }

    /**
     * Checks whether this User saved the given legacy Bed as a favorite.
     */
    public function hasFavorited(Bed $bed): bool
    {
        return $this->favorites()->where('bed_id', $bed->id)->exists();
    }

    /**
     * Checks whether this User saved the given Sleeping Place as a favorite.
     */
    public function hasFavoritedSleepingPlace(SleepingPlace $sleepingPlace): bool
    {
        return $this->favorites()->where('sleeping_place_id', $sleepingPlace->id)->exists();
    }
}
