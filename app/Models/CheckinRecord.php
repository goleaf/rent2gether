<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id', 'planned_time', 'actual_arrival_at', 'met_by',
    'keys_handed', 'room_shown', 'rules_explained', 'linen_provided', 'towel_provided',
    'locker_assigned', 'photos_before', 'guest_confirmed', 'host_confirmed',
    'has_issue', 'issue_description', 'issue_photos', 'status',
])]
class CheckinRecord extends Model
{
    protected $casts = [
        'actual_arrival_at' => 'datetime',
        'keys_handed' => 'boolean',
        'room_shown' => 'boolean',
        'rules_explained' => 'boolean',
        'linen_provided' => 'boolean',
        'towel_provided' => 'boolean',
        'locker_assigned' => 'boolean',
        'photos_before' => 'array',
        'guest_confirmed' => 'boolean',
        'host_confirmed' => 'boolean',
        'has_issue' => 'boolean',
        'issue_photos' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
