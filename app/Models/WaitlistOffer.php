<?php

namespace App\Models;

use Database\Factories\WaitlistOfferFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistOffer extends Model
{
    /** @use HasFactory<WaitlistOfferFactory> */
    use HasFactory;

    protected $fillable = [
        'waitlist_item_id',
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'status',
        'offered_at',
        'offer_expires_at',
        'accepted_at',
        'declined_at',
        'expired_at',
        'skipped_at',
        'current_price_per_night',
        'current_total_price',
        'current_deposit',
        'currency',
        'hold_started_at',
        'hold_expires_at',
        'notification_sent_at',
        'guest_response_message',
        'system_note',
    ];

    /**
     * Defines how Laravel converts stored Waitlist Offer attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'offered_at' => 'datetime',
            'offer_expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'expired_at' => 'datetime',
            'skipped_at' => 'datetime',
            'current_price_per_night' => 'decimal:2',
            'current_total_price' => 'decimal:2',
            'current_deposit' => 'decimal:2',
            'hold_started_at' => 'datetime',
            'hold_expires_at' => 'datetime',
            'notification_sent_at' => 'datetime',
        ];
    }

    /**
     * Links this Waitlist Offer to the Waitlist Item record used by its waitlist item relation.
     */
    public function waitlistItem(): BelongsTo
    {
        return $this->belongsTo(WaitlistItem::class);
    }

    /**
     * Links this Waitlist Offer to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Waitlist Offer to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Waitlist Offer to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Waitlist Offer to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Waitlist Offer to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Adds the active query filter for reusable Waitlist Offer lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Adds the expired query filter for reusable Waitlist Offer lookups.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    /**
     * Adds the accepted query filter for reusable Waitlist Offer lookups.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereIn('status', ['accepted', 'converted_to_booking']);
    }

    /**
     * Adds the for user query filter for reusable Waitlist Offer lookups.
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Adds the for sleeping place query filter for reusable Waitlist Offer lookups.
     */
    public function scopeForSleepingPlace(Builder $query, SleepingPlace|int $place): Builder
    {
        $placeId = $place instanceof SleepingPlace ? $place->id : $place;

        return $query->where('sleeping_place_id', $placeId);
    }

    /**
     * Adds the expiring soon query filter for reusable Waitlist Offer lookups.
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query->active()
            ->whereNotNull('offer_expires_at')
            ->where('offer_expires_at', '<=', now()->addMinutes(15));
    }
}
