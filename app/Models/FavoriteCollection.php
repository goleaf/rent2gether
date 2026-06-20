<?php

namespace App\Models;

use Database\Factories\FavoriteCollectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FavoriteCollection extends Model
{
    /** @use HasFactory<FavoriteCollectionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'icon',
        'color',
        'type',
        'city_id',
        'check_in_date',
        'check_out_date',
        'nights_count',
        'guests_count',
        'budget_min',
        'budget_max',
        'currency',
        'sort_order',
        'is_default',
        'is_pinned',
        'is_archived',
    ];

    /**
     * Defines how Laravel converts stored Favorite Collection attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'guests_count' => 'integer',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_pinned' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Links this Favorite Collection to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Favorite Collection to the City record used by its city relation.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Lists related Favorite records for this Favorite Collection.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Adds the active query filter for reusable Favorite Collection lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    /**
     * Adds the pinned query filter for reusable Favorite Collection lookups.
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Adds the archived query filter for reusable Favorite Collection lookups.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    /**
     * Adds the default query filter for reusable Favorite Collection lookups.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Adds the for user query filter for reusable Favorite Collection lookups.
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Adds the ordered query filter for reusable Favorite Collection lookups.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id');
    }

    /**
     * Adds the with counts query filter for reusable Favorite Collection lookups.
     */
    public function scopeWithCounts(Builder $query): Builder
    {
        return $query->withCount([
            'favorites',
            'favorites as available_favorites_count' => fn (Builder $favorite) => $favorite->where('is_currently_available', true),
            'favorites as unavailable_favorites_count' => fn (Builder $favorite) => $favorite->where('is_currently_available', false),
            'favorites as price_changed_favorites_count' => fn (Builder $favorite) => $favorite->where('price_changed', true),
        ]);
    }
}
