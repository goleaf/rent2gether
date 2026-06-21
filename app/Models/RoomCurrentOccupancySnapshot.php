<?php

namespace App\Models;

use Database\Factories\RoomCurrentOccupancySnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomCurrentOccupancySnapshot extends Model
{
    /** @use HasFactory<RoomCurrentOccupancySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'property_id',
        'host_user_id',
        'current_occupants_count',
        'current_bookings_count',
        'occupied_sleeping_places_count',
        'free_sleeping_places_count',
        'male_occupants_count',
        'female_occupants_count',
        'unknown_gender_occupants_count',
        'students_count',
        'workers_count',
        'tourists_count',
        'long_term_residents_count',
        'short_term_guests_count',
        'early_wakeup_count',
        'late_sleep_count',
        'night_work_count',
        'smokers_count',
        'non_smokers_count',
        'quiet_preferring_count',
        'social_count',
        'checkout_today_count',
        'checkin_today_count',
        'checkout_this_week_count',
        'has_open_complaints',
        'has_open_maintenance',
        'has_noise_warning',
        'has_cleanliness_warning',
        'last_recalculated_at',
    ];

    protected function casts(): array
    {
        return [
            'current_occupants_count' => 'integer',
            'current_bookings_count' => 'integer',
            'occupied_sleeping_places_count' => 'integer',
            'free_sleeping_places_count' => 'integer',
            'male_occupants_count' => 'integer',
            'female_occupants_count' => 'integer',
            'unknown_gender_occupants_count' => 'integer',
            'students_count' => 'integer',
            'workers_count' => 'integer',
            'tourists_count' => 'integer',
            'long_term_residents_count' => 'integer',
            'short_term_guests_count' => 'integer',
            'early_wakeup_count' => 'integer',
            'late_sleep_count' => 'integer',
            'night_work_count' => 'integer',
            'smokers_count' => 'integer',
            'non_smokers_count' => 'integer',
            'quiet_preferring_count' => 'integer',
            'social_count' => 'integer',
            'checkout_today_count' => 'integer',
            'checkin_today_count' => 'integer',
            'checkout_this_week_count' => 'integer',
            'has_open_complaints' => 'boolean',
            'has_open_maintenance' => 'boolean',
            'has_noise_warning' => 'boolean',
            'has_cleanliness_warning' => 'boolean',
            'last_recalculated_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
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
