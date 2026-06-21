<?php

namespace App\Models;

use Database\Factories\HostUnresponsiveMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostUnresponsiveMedia extends Model
{
    /** @use HasFactory<HostUnresponsiveMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'host_unresponsive_case_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'guest_and_host',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(BookingHostUnresponsiveCase::class, 'host_unresponsive_case_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
