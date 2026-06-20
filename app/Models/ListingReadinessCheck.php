<?php

namespace App\Models;

use Database\Factories\ListingReadinessCheckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingReadinessCheck extends Model
{
    /** @use HasFactory<ListingReadinessCheckFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'check_key',
        'status',
        'required',
        'message_key',
        'message_params_json',
    ];

    /**
     * Defines how Laravel converts stored Listing Readiness Check attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'message_params_json' => 'array',
        ];
    }

    /**
     * Links this Listing Readiness Check to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Listing Readiness Check to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Listing Readiness Check to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Listing Readiness Check to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
