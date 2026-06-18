<?php

namespace App\Models;

use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'name', 'city', 'district', 'check_in', 'check_out', 'nights',
    'price_min', 'price_max', 'room_type', 'bed_type', 'amenities', 'filters',
    'notify_new_places', 'notify_price_drop', 'notify_available', 'notify_frequency', 'is_active',
])]
class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasFactory;

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'amenities' => 'array',
        'filters' => 'array',
        'notify_new_places' => 'boolean',
        'notify_price_drop' => 'boolean',
        'notify_available' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
