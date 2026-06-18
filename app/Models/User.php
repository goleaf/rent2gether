<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'phone', 'phone_verified', 'avatar', 'date_of_birth',
    'gender', 'country', 'city', 'languages', 'bio', 'occupation', 'travel_purpose',
    'is_smoker', 'has_pets', 'has_allergies', 'prefers_quiet', 'sleep_schedule',
    'willing_to_share_room', 'preferred_room_gender',
    'identity_verified', 'identity_verified_at',
    'is_host', 'host_description', 'host_experience_years', 'host_lives_on_site',
    'preferred_contact_method',
    'rating_as_guest', 'rating_as_host', 'completed_stays_count', 'hosted_stays_count',
    'cancellations_count', 'complaints_count', 'status', 'last_active_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    // Host relationships
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'host_id');
    }

    // Guest relationships
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'guest_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    // Shared relationships
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

    public function complaintsReported(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }

    public function complaintsAgainst(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reported_user_id');
    }

    // Scopes
    public function scopeHosts(Builder $query): void
    {
        $query->where('is_host', true);
    }

    public function scopeVerified(Builder $query): void
    {
        $query->where('identity_verified', true);
    }

    // Helpers
    public function isHost(): bool
    {
        return $this->is_host;
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function profileCompleteness(): int
    {
        $fields = ['name', 'avatar', 'phone', 'date_of_birth', 'country', 'city', 'languages', 'bio'];
        $filled = collect($fields)->filter(fn (string $f) => ! empty($this->$f))->count();

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
}
