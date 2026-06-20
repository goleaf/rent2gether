<?php

namespace App\Models;

use Database\Factories\FavoriteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    /** @use HasFactory<FavoriteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favorite_collection_id',
        'property_id',
        'room_id',
        'bed_id',
        'sleeping_place_id',
        'source',
        'collection',
        'note',
        'personal_note',
        'short_label',
        'label_color',
        'priority',
        'decision_status',
        'price_at_save',
        'check_in',
        'check_out',
        'check_in_date',
        'check_out_date',
        'nights_count',
        'guests_count',
        'currency',
        'price_per_night_snapshot',
        'total_price_snapshot',
        'deposit_snapshot',
        'discount_snapshot',
        'current_price_per_night',
        'current_total_price',
        'current_deposit',
        'price_changed',
        'price_change_amount',
        'price_change_percent',
        'price_last_checked_at',
        'was_available_when_added',
        'is_currently_available',
        'became_unavailable',
        'became_available_again',
        'partial_availability',
        'nearest_available_dates_json',
        'availability_last_checked_at',
        'remind_at',
        'reminder_text',
        'reminder_sent_at',
        'notify_available',
        'notify_price_drop',
        'notify_price_increase',
        'notify_available_again',
        'notify_unavailable',
        'last_viewed_at',
        'added_at',
    ];

    /**
     * Defines how Laravel converts stored Favorite attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'price_at_save' => 'decimal:2',
            'check_in' => 'date',
            'check_out' => 'date',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'guests_count' => 'integer',
            'priority' => 'integer',
            'price_per_night_snapshot' => 'decimal:2',
            'total_price_snapshot' => 'decimal:2',
            'deposit_snapshot' => 'decimal:2',
            'discount_snapshot' => 'decimal:2',
            'current_price_per_night' => 'decimal:2',
            'current_total_price' => 'decimal:2',
            'current_deposit' => 'decimal:2',
            'price_changed' => 'boolean',
            'price_change_amount' => 'decimal:2',
            'price_change_percent' => 'decimal:2',
            'price_last_checked_at' => 'datetime',
            'was_available_when_added' => 'boolean',
            'is_currently_available' => 'boolean',
            'became_unavailable' => 'boolean',
            'became_available_again' => 'boolean',
            'partial_availability' => 'boolean',
            'nearest_available_dates_json' => 'array',
            'availability_last_checked_at' => 'datetime',
            'remind_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'notify_available' => 'boolean',
            'notify_price_drop' => 'boolean',
            'notify_price_increase' => 'boolean',
            'notify_available_again' => 'boolean',
            'notify_unavailable' => 'boolean',
            'last_viewed_at' => 'datetime',
            'added_at' => 'datetime',
        ];
    }

    /**
     * Links this Favorite to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Favorite to the Favorite Collection record used by its favorite collection relation.
     */
    public function favoriteCollection(): BelongsTo
    {
        return $this->belongsTo(FavoriteCollection::class);
    }

    /**
     * Links this Favorite to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Favorite to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Favorite to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    /**
     * Links this Favorite to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Adds the for guest query filter for reusable Favorite lookups.
     */
    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Adds the for user query filter for reusable Favorite lookups.
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Adds the in collection query filter for reusable Favorite lookups.
     */
    public function scopeInCollection(Builder $query, FavoriteCollection|int $collection): Builder
    {
        $collectionId = $collection instanceof FavoriteCollection ? $collection->id : $collection;

        return $query->where('favorite_collection_id', $collectionId);
    }

    /**
     * Adds the available query filter for reusable Favorite lookups.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_currently_available', true);
    }

    /**
     * Adds the unavailable query filter for reusable Favorite lookups.
     */
    public function scopeUnavailable(Builder $query): Builder
    {
        return $query->where('is_currently_available', false);
    }

    /**
     * Adds the price changed query filter for reusable Favorite lookups.
     */
    public function scopePriceChanged(Builder $query): Builder
    {
        return $query->where('price_changed', true);
    }

    /**
     * Adds the price dropped query filter for reusable Favorite lookups.
     */
    public function scopePriceDropped(Builder $query): Builder
    {
        return $query->where('price_change_amount', '<', 0);
    }

    /**
     * Adds the price increased query filter for reusable Favorite lookups.
     */
    public function scopePriceIncreased(Builder $query): Builder
    {
        return $query->where('price_change_amount', '>', 0);
    }

    /**
     * Adds the high priority query filter for reusable Favorite lookups.
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', '>=', 8);
    }

    /**
     * Adds the decision status query filter for reusable Favorite lookups.
     */
    public function scopeDecisionStatus(Builder $query, string $status): Builder
    {
        return $query->where('decision_status', $status);
    }

    /**
     * Adds the with reminder query filter for reusable Favorite lookups.
     */
    public function scopeWithReminder(Builder $query): Builder
    {
        return $query->whereNotNull('remind_at');
    }

    /**
     * Adds the recently added query filter for reusable Favorite lookups.
     */
    public function scopeRecentlyAdded(Builder $query): Builder
    {
        return $query->orderByDesc('added_at')->orderByDesc('created_at');
    }

    /**
     * Returns the saved note text for this Favorite.
     */
    public function noteText(): ?string
    {
        return $this->personal_note ?: $this->note;
    }
}
