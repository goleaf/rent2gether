<?php

namespace App\Models;

use Database\Factories\BookingStayOccupantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStayOccupant extends Model
{
    /** @use HasFactory<BookingStayOccupantFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_stay_id',
        'booking_id',
        'user_id',
        'occupant_name',
        'occupant_type',
        'is_main_guest',
        'age_range',
        'gender',
        'public_gender_visible',
        'city_name',
        'country_name',
        'languages_json',
        'stay_purpose',
        'sleep_schedule',
        'smoking_status',
        'sociability_level',
        'neighbor_rating_snapshot',
        'public_visibility',
    ];

    protected function casts(): array
    {
        return [
            'is_main_guest' => 'boolean',
            'public_gender_visible' => 'boolean',
            'languages_json' => 'array',
            'neighbor_rating_snapshot' => 'decimal:2',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function bookingStay(): BelongsTo
    {
        return $this->stay();
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
