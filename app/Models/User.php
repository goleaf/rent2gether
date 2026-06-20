<?php

namespace App\Models;

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
        'avatar',
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified' => 'boolean',
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
            'host_lives_on_site' => 'boolean',
            'rating_as_guest' => 'decimal:2',
            'rating_as_host' => 'decimal:2',
            'status' => UserStatus::class,
            'last_active_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function guestPreference(): HasOne
    {
        return $this->hasOne(GuestPreference::class);
    }

    public function guestCompatibilityProfile(): HasOne
    {
        return $this->hasOne(GuestCompatibilityProfile::class);
    }

    public function guestCompatibilityVisibilitySetting(): HasOne
    {
        return $this->hasOne(GuestCompatibilityVisibilitySetting::class);
    }

    public function hostProfile(): HasOne
    {
        return $this->hasOne(HostProfile::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'host_user_id');
    }

    public function legacyProperties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'host_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'guest_user_id');
    }

    public function bookingGuestIntakes(): HasMany
    {
        return $this->hasMany(BookingGuestIntake::class);
    }

    public function hostedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'host_user_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteCollections(): HasMany
    {
        return $this->hasMany(FavoriteCollection::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    public function waitlistItems(): HasMany
    {
        return $this->hasMany(WaitlistItem::class);
    }

    public function coLivingProfile(): HasOne
    {
        return $this->hasOne(CoLivingProfile::class);
    }

    public function coLivingVisibilitySetting(): HasOne
    {
        return $this->hasOne(CoLivingVisibilitySetting::class);
    }

    public function roomOccupantSnapshots(): HasMany
    {
        return $this->hasMany(RoomOccupantSnapshot::class);
    }

    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(CompatibilityResult::class);
    }

    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'participant_one_id');
    }

    public function guestMessageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class, 'guest_user_id');
    }

    public function hostMessageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class, 'host_user_id');
    }

    public function complaintsReported(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }

    public function complaintsAgainst(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reported_user_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hostHintSnapshots(): HasMany
    {
        return $this->hasMany(HostHintSnapshot::class);
    }

    public function hostHintDismissals(): HasMany
    {
        return $this->hasMany(HostHintDismissal::class);
    }

    public function hostHintActions(): HasMany
    {
        return $this->hasMany(HostHintAction::class);
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    public function avatarMedia(): MorphOne
    {
        return $this->morphOne(MediaItem::class, 'mediable')
            ->active()
            ->where('collection', 'avatar')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeHosts(Builder $query): Builder
    {
        return $query->where('is_host', true);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('identity_verified', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active->value);
    }

    public function isHost(): bool
    {
        return (bool) $this->is_host || $this->hostProfile()->exists();
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function profileCompleteness(): int
    {
        $fields = ['name', 'avatar', 'phone', 'date_of_birth', 'country', 'city', 'languages', 'bio'];
        $filled = collect($fields)->filter(fn (string $field) => ! empty($this->{$field}))->count();

        return (int) round(($filled / count($fields)) * 100);
    }

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

    public function hasFavorited(Bed $bed): bool
    {
        return $this->favorites()->where('bed_id', $bed->id)->exists();
    }

    public function hasFavoritedSleepingPlace(SleepingPlace $sleepingPlace): bool
    {
        return $this->favorites()->where('sleeping_place_id', $sleepingPlace->id)->exists();
    }
}
