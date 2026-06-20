<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'booking_id', 'planned_time', 'actual_arrival_at', 'met_by',
    'property_found',
    'keys_handed', 'room_shown', 'rules_explained', 'linen_provided', 'towel_provided',
    'keys_received', 'code_received', 'sleeping_place_shown', 'everything_ok',
    'locker_assigned', 'photos_before', 'guest_confirmed', 'host_confirmed',
    'guest_confirmed_at', 'host_confirmed_at',
    'has_issue', 'issue_description', 'issue_photos', 'status',
    'problem_reported', 'problem_description', 'problem_media',
])]
class CheckinRecord extends Model
{
    protected $casts = [
        'actual_arrival_at' => 'datetime',
        'property_found' => 'boolean',
        'keys_handed' => 'boolean',
        'keys_received' => 'boolean',
        'code_received' => 'boolean',
        'room_shown' => 'boolean',
        'sleeping_place_shown' => 'boolean',
        'rules_explained' => 'boolean',
        'everything_ok' => 'boolean',
        'linen_provided' => 'boolean',
        'towel_provided' => 'boolean',
        'locker_assigned' => 'boolean',
        'photos_before' => 'array',
        'guest_confirmed' => 'boolean',
        'host_confirmed' => 'boolean',
        'guest_confirmed_at' => 'datetime',
        'host_confirmed_at' => 'datetime',
        'has_issue' => 'boolean',
        'issue_photos' => 'array',
        'problem_reported' => 'boolean',
        'problem_media' => 'array',
    ];

    /**
     * Links this Checkin Record to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Lists related Media Item records attached to this Checkin Record through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }
}
