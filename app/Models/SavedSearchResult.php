<?php

namespace App\Models;

use Database\Factories\SavedSearchResultFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearchResult extends Model
{
    /** @use HasFactory<SavedSearchResultFactory> */
    use HasFactory;

    protected $fillable = [
        'saved_search_id',
        'sleeping_place_id',
        'property_id',
        'room_id',
        'first_seen_at',
        'last_seen_at',
        'last_matched_at',
        'status',
        'match_score',
        'price_per_night_snapshot',
        'total_price_snapshot',
        'current_price_per_night',
        'current_total_price',
        'deposit_snapshot',
        'current_deposit',
        'price_changed',
        'price_change_amount',
        'price_change_percent',
        'became_unavailable',
        'became_available_again',
        'is_new_match',
        'is_notified',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_matched_at' => 'datetime',
            'match_score' => 'integer',
            'price_per_night_snapshot' => 'decimal:2',
            'total_price_snapshot' => 'decimal:2',
            'current_price_per_night' => 'decimal:2',
            'current_total_price' => 'decimal:2',
            'deposit_snapshot' => 'decimal:2',
            'current_deposit' => 'decimal:2',
            'price_changed' => 'boolean',
            'price_change_amount' => 'decimal:2',
            'price_change_percent' => 'decimal:2',
            'became_unavailable' => 'boolean',
            'became_available_again' => 'boolean',
            'is_new_match' => 'boolean',
            'is_notified' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function savedSearch(): BelongsTo
    {
        return $this->belongsTo(SavedSearch::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function scopeNewMatches(Builder $query): Builder
    {
        return $query->where('is_new_match', true);
    }

    public function scopePriceChanged(Builder $query): Builder
    {
        return $query->where('price_changed', true);
    }

    public function scopePriceDropped(Builder $query): Builder
    {
        return $query->where('price_changed', true)->where('price_change_amount', '<', 0);
    }

    public function scopeAvailableAgain(Builder $query): Builder
    {
        return $query->where('became_available_again', true);
    }

    public function scopeNotNotified(Builder $query): Builder
    {
        return $query->where('is_notified', false);
    }

    public function scopeRecentlyMatched(Builder $query): Builder
    {
        return $query->orderByDesc('last_matched_at')->orderByDesc('id');
    }
}
