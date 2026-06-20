<?php

namespace App\Models;

use Database\Factories\PropertyLocationDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyLocationDetail extends Model
{
    /** @use HasFactory<PropertyLocationDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'nearest_metro',
        'nearest_metro_distance_meters',
        'nearest_metro_walk_minutes',
        'nearest_bus_stop',
        'nearest_bus_stop_distance_meters',
        'nearest_bus_stop_walk_minutes',
        'nearest_tram_stop',
        'nearest_train_station',
        'nearest_railway_station',
        'railway_station_distance_meters',
        'nearest_airport',
        'airport_distance_meters',
        'airport_transport_minutes',
        'nearest_shop',
        'nearest_supermarket',
        'nearest_pharmacy',
        'nearest_hospital',
        'nearest_clinic',
        'nearest_university',
        'nearest_school',
        'nearest_gym',
        'nearest_park',
        'nearest_mall',
        'nearest_cafe',
        'nearest_laundry',
        'nearest_atm',
        'nearest_coworking',
        'distance_to_center_meters',
        'walk_minutes_to_center',
        'transport_minutes_to_center',
        'car_minutes_to_center',
        'transport_convenience_level',
        'has_night_transport',
        'easy_to_reach_with_luggage',
        'district_noise_level',
        'district_safety_level',
        'street_lighting_level',
        'street_busy_at_night',
        'has_street_noise',
        'has_bar_noise',
        'has_train_noise',
        'has_construction_nearby',
        'has_parking_nearby',
        'has_free_parking',
        'has_paid_parking',
        'has_private_parking',
        'has_yard_parking',
        'has_underground_parking',
        'has_bicycle_parking',
        'parking_permit_required',
        'parking_usually_full',
    ];

    /**
     * Defines how Laravel converts stored Property Location Detail attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'nearest_metro_distance_meters' => 'integer',
            'nearest_metro_walk_minutes' => 'integer',
            'nearest_bus_stop_distance_meters' => 'integer',
            'nearest_bus_stop_walk_minutes' => 'integer',
            'railway_station_distance_meters' => 'integer',
            'airport_distance_meters' => 'integer',
            'airport_transport_minutes' => 'integer',
            'distance_to_center_meters' => 'integer',
            'walk_minutes_to_center' => 'integer',
            'transport_minutes_to_center' => 'integer',
            'car_minutes_to_center' => 'integer',
            'has_night_transport' => 'boolean',
            'easy_to_reach_with_luggage' => 'boolean',
            'street_busy_at_night' => 'boolean',
            'has_street_noise' => 'boolean',
            'has_bar_noise' => 'boolean',
            'has_train_noise' => 'boolean',
            'has_construction_nearby' => 'boolean',
            'has_parking_nearby' => 'boolean',
            'has_free_parking' => 'boolean',
            'has_paid_parking' => 'boolean',
            'has_private_parking' => 'boolean',
            'has_yard_parking' => 'boolean',
            'has_underground_parking' => 'boolean',
            'has_bicycle_parking' => 'boolean',
            'parking_permit_required' => 'boolean',
            'parking_usually_full' => 'boolean',
        ];
    }

    /**
     * Links this Property Location Detail to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
