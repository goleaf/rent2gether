<?php

namespace App\Models;

use Database\Factories\PropertyAmenityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAmenity extends Model
{
    /** @use HasFactory<PropertyAmenityFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'amenity_key',
        'available',
        'description',
        'visible_to_guest',
    ];

    /**
     * Defines how Laravel converts stored Property Amenity attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'available' => 'boolean',
            'visible_to_guest' => 'boolean',
        ];
    }

    /**
     * Links this Property Amenity to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
