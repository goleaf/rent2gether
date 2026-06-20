<?php

namespace App\Models;

use Database\Factories\WaitlistItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WaitlistItem extends Model
{
    /** @use HasFactory<WaitlistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'source',
        'desired_check_in',
        'desired_check_out',
        'desired_check_in_date',
        'desired_check_out_date',
        'nights_count',
        'calendar_days_count',
        'guests_count',
        'flexible_dates',
        'flexible_days',
        'min_nights',
        'max_nights',
        'max_price',
        'max_price_per_night',
        'max_total_price',
        'max_deposit',
        'currency',
        'price_at_join',
        'ready_to_book',
        'ready_to_book_immediately',
        'ready_to_pay_immediately',
        'auto_request',
        'auto_send_request',
        'auto_create_booking_draft',
        'notify_available',
        'notify_price_drop',
        'notify_similar_available',
        'notify_offer_expiring',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'guest_message',
        'position',
        'priority_score',
        'offered_count',
        'skipped_count',
        'max_skips',
        'last_offered_at',
        'last_notified_at',
        'expires_at',
        'last_checked_at',
        'added_at',
        'cancelled_at',
        'completed_at',
        'notified',
        'notified_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'desired_check_in' => 'date',
            'desired_check_out' => 'date',
            'desired_check_in_date' => 'date:Y-m-d',
            'desired_check_out_date' => 'date:Y-m-d',
            'nights_count' => 'integer',
            'calendar_days_count' => 'integer',
            'guests_count' => 'integer',
            'flexible_dates' => 'boolean',
            'flexible_days' => 'integer',
            'min_nights' => 'integer',
            'max_nights' => 'integer',
            'max_price' => 'decimal:2',
            'max_price_per_night' => 'decimal:2',
            'max_total_price' => 'decimal:2',
            'max_deposit' => 'decimal:2',
            'price_at_join' => 'decimal:2',
            'ready_to_book' => 'boolean',
            'ready_to_book_immediately' => 'boolean',
            'ready_to_pay_immediately' => 'boolean',
            'auto_request' => 'boolean',
            'auto_send_request' => 'boolean',
            'auto_create_booking_draft' => 'boolean',
            'notify_available' => 'boolean',
            'notify_price_drop' => 'boolean',
            'notify_similar_available' => 'boolean',
            'notify_offer_expiring' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'position' => 'integer',
            'priority_score' => 'integer',
            'offered_count' => 'integer',
            'skipped_count' => 'integer',
            'max_skips' => 'integer',
            'last_offered_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'added_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'notified' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (WaitlistItem $item): void {
            $item->desired_check_in_date ??= $item->desired_check_in;
            $item->desired_check_out_date ??= $item->desired_check_out;
            $item->desired_check_in ??= $item->desired_check_in_date;
            $item->desired_check_out ??= $item->desired_check_out_date;

            $item->max_price_per_night ??= $item->max_price;
            $item->max_price ??= $item->max_price_per_night;
            $readyToBook = (bool) $item->ready_to_book || (bool) $item->ready_to_book_immediately;
            $autoRequest = (bool) $item->auto_request || (bool) $item->auto_send_request;
            $item->ready_to_book_immediately = $readyToBook;
            $item->ready_to_book = $readyToBook;
            $item->auto_send_request = $autoRequest;
            $item->auto_request = $autoRequest;
            $item->added_at ??= now();

            if (($item->property_id === null || $item->room_id === null) && $item->sleeping_place_id !== null) {
                $place = SleepingPlace::query()
                    ->select(['id', 'property_id', 'room_id'])
                    ->find($item->sleeping_place_id);

                if ($place instanceof SleepingPlace) {
                    $item->property_id ??= $place->property_id;
                    $item->room_id ??= $place->room_id;
                }
            }

            if ($item->desired_check_in_date && $item->desired_check_out_date) {
                $nights = max(0, (int) $item->desired_check_in_date->diffInDays($item->desired_check_out_date));
                $item->nights_count ??= $nights;
                $item->calendar_days_count ??= $nights + 1;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(WaitlistOffer::class);
    }

    public function activeOffer(): HasOne
    {
        return $this->hasOne(WaitlistOffer::class)
            ->where('waitlist_offers.status', 'active')
            ->orderByDesc('waitlist_offers.id');
    }

    public function scopeForGuest(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'waiting']);
    }

    public function scopeOffered(Builder $query): Builder
    {
        return $query->whereIn('status', ['offered', 'awaiting_guest']);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    public function scopeForSleepingPlace(Builder $query, SleepingPlace|int $place): Builder
    {
        $placeId = $place instanceof SleepingPlace ? $place->id : $place;

        return $query->where('sleeping_place_id', $placeId);
    }

    public function scopeDueForCheck(Builder $query): Builder
    {
        return $query->active()
            ->where(function (Builder $builder): void {
                $builder->whereNull('last_checked_at')
                    ->orWhere('last_checked_at', '<=', now()->subMinutes(15));
            });
    }

    public function scopeOrderedQueue(Builder $query): Builder
    {
        return $query
            ->orderByDesc('priority_score')
            ->orderBy('added_at')
            ->orderBy('id');
    }

    public function scopeReadyToBook(Builder $query): Builder
    {
        return $query->where('ready_to_book_immediately', true);
    }

    public function scopeAutoSendRequestEnabled(Builder $query): Builder
    {
        return $query->where('auto_send_request', true);
    }

    protected function queueStatusLabel(): Attribute
    {
        return Attribute::get(fn (): string => __('waitlist.statuses.'.$this->status));
    }
}
