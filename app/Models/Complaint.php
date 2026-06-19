<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    protected $fillable = [
        'reference',
        'reporter_id',
        'reported_user_id',
        'booking_id',
        'property_id',
        'room_id',
        'bed_id',
        'sleeping_place_id',
        'type',
        'description',
        'photos',
        'urgency',
        'desired_resolution',
        'respondent_reply',
        'resolution_notes',
        'compensation_amount',
        'refund_amount',
        'deposit_withheld',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => ComplaintType::class,
            'status' => ComplaintStatus::class,
            'photos' => 'array',
            'compensation_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'deposit_withheld' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint): void {
            if (empty($complaint->reference)) {
                $complaint->reference = strtoupper('CMP-'.now()->format('ymd').'-'.substr(uniqid(), -5));
            }
        });
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
