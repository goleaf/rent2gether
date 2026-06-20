<?php

namespace App\Models;

use Database\Factories\PropertyAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAddress extends Model
{
    /** @use HasFactory<PropertyAddressFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'country_id',
        'city_id',
        'district_id',
        'street_name',
        'house_number',
        'apartment_number',
        'postal_code',
        'floor',
        'latitude',
        'longitude',
        'approximate_latitude',
        'approximate_longitude',
        'public_location_label',
        'show_exact_address_after_booking',
        'show_street_before_booking',
        'show_district_before_booking',
    ];

    /**
     * Defines how Laravel converts stored Property Address attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'approximate_latitude' => 'decimal:7',
            'approximate_longitude' => 'decimal:7',
            'show_exact_address_after_booking' => 'boolean',
            'show_street_before_booking' => 'boolean',
            'show_district_before_booking' => 'boolean',
        ];
    }

    /**
     * Links this Property Address to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
