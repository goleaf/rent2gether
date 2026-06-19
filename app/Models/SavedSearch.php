<?php

namespace App\Models;

use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id',
        'locale',
        'name',
        'city',
        'district',
        'check_in',
        'check_out',
        'nights',
        'price_min',
        'price_max',
        'room_type',
        'bed_type',
        'amenities',
        'filters',
        'filters_json',
        'notify_new_places',
        'notify_price_drop',
        'notify_available',
        'notify_frequency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'amenities' => 'array',
            'filters' => 'array',
            'filters_json' => 'array',
            'notify_new_places' => 'boolean',
            'notify_price_drop' => 'boolean',
            'notify_available' => 'boolean',
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

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
