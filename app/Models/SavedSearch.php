<?php

namespace App\Models;

use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id',
        'locale',
        'name',
        'title',
        'description',
        'status',
        'city',
        'district',
        'location_text',
        'radius_meters',
        'check_in',
        'check_out',
        'check_in_date',
        'check_out_date',
        'flexible_dates',
        'flexible_days',
        'nights',
        'nights_count',
        'calendar_days_count',
        'guests_count',
        'price_min',
        'price_max',
        'budget_min',
        'budget_max',
        'total_budget_max',
        'currency',
        'room_type',
        'bed_type',
        'amenities',
        'filters',
        'filters_json',
        'property_types_json',
        'room_types_json',
        'sleeping_place_types_json',
        'room_gender_policy',
        'required_amenity_ids_json',
        'preferred_amenity_ids_json',
        'excluded_rule_ids_json',
        'excluded_conditions_json',
        'only_verified_hosts',
        'only_verified_places',
        'only_instant_booking',
        'only_with_reviews',
        'free_cancellation_only',
        'no_deposit_only',
        'max_deposit',
        'min_rating',
        'min_cleanliness_rating',
        'min_safety_rating',
        'min_host_rating',
        'max_people_in_room',
        'lower_bunk_only',
        'exclude_upper_bunk',
        'exclude_sofa',
        'exclude_mattress',
        'require_locker',
        'require_workspace',
        'require_wifi',
        'require_kitchen',
        'require_washing_machine',
        'require_late_check_in',
        'avoid_smoking',
        'avoid_pets',
        'avoid_mixed_room',
        'notify_new_places',
        'notify_price_drop',
        'notify_available',
        'notify_frequency',
        'notify_new_matches',
        'notify_price_drops',
        'notify_price_increases',
        'notify_available_again',
        'notify_better_match',
        'notification_frequency',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'last_checked_at',
        'last_notified_at',
        'next_check_at',
        'new_matches_count',
        'price_drops_count',
        'available_again_count',
        'last_results_hash',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'flexible_dates' => 'boolean',
            'radius_meters' => 'integer',
            'flexible_days' => 'integer',
            'nights' => 'integer',
            'nights_count' => 'integer',
            'calendar_days_count' => 'integer',
            'guests_count' => 'integer',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'total_budget_max' => 'decimal:2',
            'amenities' => 'array',
            'filters' => 'array',
            'filters_json' => 'array',
            'property_types_json' => 'array',
            'room_types_json' => 'array',
            'sleeping_place_types_json' => 'array',
            'required_amenity_ids_json' => 'array',
            'preferred_amenity_ids_json' => 'array',
            'excluded_rule_ids_json' => 'array',
            'excluded_conditions_json' => 'array',
            'only_verified_hosts' => 'boolean',
            'only_verified_places' => 'boolean',
            'only_instant_booking' => 'boolean',
            'only_with_reviews' => 'boolean',
            'free_cancellation_only' => 'boolean',
            'no_deposit_only' => 'boolean',
            'max_deposit' => 'decimal:2',
            'min_rating' => 'decimal:2',
            'min_cleanliness_rating' => 'decimal:2',
            'min_safety_rating' => 'decimal:2',
            'min_host_rating' => 'decimal:2',
            'max_people_in_room' => 'integer',
            'lower_bunk_only' => 'boolean',
            'exclude_upper_bunk' => 'boolean',
            'exclude_sofa' => 'boolean',
            'exclude_mattress' => 'boolean',
            'require_locker' => 'boolean',
            'require_workspace' => 'boolean',
            'require_wifi' => 'boolean',
            'require_kitchen' => 'boolean',
            'require_washing_machine' => 'boolean',
            'require_late_check_in' => 'boolean',
            'avoid_smoking' => 'boolean',
            'avoid_pets' => 'boolean',
            'avoid_mixed_room' => 'boolean',
            'notify_new_places' => 'boolean',
            'notify_price_drop' => 'boolean',
            'notify_available' => 'boolean',
            'notify_new_matches' => 'boolean',
            'notify_price_drops' => 'boolean',
            'notify_price_increases' => 'boolean',
            'notify_available_again' => 'boolean',
            'notify_better_match' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'last_checked_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'next_check_at' => 'datetime',
            'new_matches_count' => 'integer',
            'price_drops_count' => 'integer',
            'available_again_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cityModel(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(SavedSearchResult::class);
    }

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopePaused(Builder $query): Builder
    {
        return $query->where('status', 'paused');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeDueForCheck(Builder $query): Builder
    {
        return $query->active()
            ->where(function (Builder $builder): void {
                $builder->whereNull('next_check_at')
                    ->orWhere('next_check_at', '<=', now());
            });
    }

    public function scopeWithNotificationsEnabled(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where('notify_new_matches', true)
                ->orWhere('notify_price_drops', true)
                ->orWhere('notify_available_again', true)
                ->orWhere('notify_better_match', true);
        });
    }

    public function displayTitle(): string
    {
        return $this->title ?: $this->name;
    }
}
