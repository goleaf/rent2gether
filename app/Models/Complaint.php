<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_number',
        'reference',
        'reporter_user_id',
        'reporter_id',
        'reported_user_id',
        'booking_id',
        'property_id',
        'room_id',
        'bed_id',
        'sleeping_place_id',
        'type',
        'priority',
        'description',
        'media',
        'photos',
        'urgency',
        'desired_resolution',
        'refund_requested',
        'deposit_hold_requested',
        'other_side_response',
        'respondent_reply',
        'resolution_text',
        'resolution_notes',
        'compensation_amount',
        'refund_amount',
        'deposit_hold_amount',
        'deposit_withheld',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => ComplaintType::class,
            'status' => ComplaintStatus::class,
            'media' => 'array',
            'photos' => 'array',
            'refund_requested' => 'boolean',
            'deposit_hold_requested' => 'boolean',
            'compensation_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'deposit_hold_amount' => 'decimal:2',
            'deposit_withheld' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint): void {
            if (empty($complaint->reference) && empty($complaint->complaint_number)) {
                $complaint->complaint_number = strtoupper('CMP-'.now()->format('ymd').'-'.substr(uniqid(), -5));
            }

            $complaint->reference ??= $complaint->complaint_number;
            $complaint->complaint_number ??= $complaint->reference;
        });

        static::saving(function (Complaint $complaint): void {
            $complaint->reference ??= $complaint->complaint_number;
            $complaint->complaint_number ??= $complaint->reference;
            $complaint->reporter_id ??= $complaint->reporter_user_id;
            $complaint->reporter_user_id ??= $complaint->reporter_id;
            $complaint->urgency ??= $complaint->priority;
            $complaint->priority ??= $complaint->urgency;
            $complaint->photos ??= $complaint->media;
            $complaint->media ??= $complaint->photos;
            $complaint->respondent_reply ??= $complaint->other_side_response;
            $complaint->other_side_response ??= $complaint->respondent_reply;
            $complaint->resolution_notes ??= $complaint->resolution_text;
            $complaint->resolution_text ??= $complaint->resolution_notes;
            $complaint->deposit_withheld ??= $complaint->deposit_hold_amount;
            $complaint->deposit_hold_amount ??= $complaint->deposit_withheld;
        });
    }

    public function scopeForParticipant(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId): void {
            $query->where('reporter_user_id', $userId)
                ->orWhere('reporter_id', $userId)
                ->orWhere('reported_user_id', $userId);
        });
    }

    public function scopeForBooking(Builder $query, Booking|int $booking): Builder
    {
        $bookingId = $booking instanceof Booking ? $booking->id : $booking;

        return $query->where('booking_id', $bookingId);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reporterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
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

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ComplaintStatusHistory::class);
    }
}
