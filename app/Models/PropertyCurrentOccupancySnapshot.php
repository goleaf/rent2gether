<?php

namespace App\Models;

use Database\Factories\PropertyCurrentOccupancySnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyCurrentOccupancySnapshot extends Model
{
    /** @use HasFactory<PropertyCurrentOccupancySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'host_user_id',
        'current_occupants_count',
        'current_bookings_count',
        'occupied_rooms_count',
        'occupied_sleeping_places_count',
        'free_sleeping_places_count',
        'checkout_today_count',
        'checkin_today_count',
        'checkout_this_week_count',
        'has_open_complaints',
        'has_open_maintenance',
        'has_cleaning_needed',
        'has_inspection_needed',
        'last_recalculated_at',
    ];

    protected function casts(): array
    {
        return [
            'current_occupants_count' => 'integer',
            'current_bookings_count' => 'integer',
            'occupied_rooms_count' => 'integer',
            'occupied_sleeping_places_count' => 'integer',
            'free_sleeping_places_count' => 'integer',
            'checkout_today_count' => 'integer',
            'checkin_today_count' => 'integer',
            'checkout_this_week_count' => 'integer',
            'has_open_complaints' => 'boolean',
            'has_open_maintenance' => 'boolean',
            'has_cleaning_needed' => 'boolean',
            'has_inspection_needed' => 'boolean',
            'last_recalculated_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
