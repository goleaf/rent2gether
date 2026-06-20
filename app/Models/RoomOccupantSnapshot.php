<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\RoomOccupantSnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomOccupantSnapshot extends Model
{
    /** @use HasFactory<RoomOccupantSnapshotFactory> */
    use HasFactory;

    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_CURRENT = 'current';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_LEAVING_SOON = 'leaving_soon';

    public const STATUS_CHECKED_OUT = 'checked_out';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'user_id',
        'status',
        'check_in_date',
        'check_out_date',
        'public_alias_snapshot',
        'age_range_snapshot',
        'gender_for_room_policy_snapshot',
        'country_label_snapshot',
        'city_label_snapshot',
        'languages_json_snapshot',
        'stay_purpose_snapshot',
        'guest_type_snapshot',
        'tourist_snapshot',
        'student_snapshot',
        'working_snapshot',
        'remote_worker_snapshot',
        'long_term_guest_snapshot',
        'short_term_guest_snapshot',
        'sleep_schedule_snapshot',
        'wake_schedule_snapshot',
        'home_presence_level_snapshot',
        'smokes_snapshot',
        'social_level_snapshot',
        'prefers_quiet_snapshot',
        'roommate_rating_average_snapshot',
        'roommate_reviews_count_snapshot',
        'privacy_level',
        'can_show_before_booking',
        'can_show_after_booking',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'languages_json_snapshot' => 'array',
            'tourist_snapshot' => 'boolean',
            'student_snapshot' => 'boolean',
            'working_snapshot' => 'boolean',
            'remote_worker_snapshot' => 'boolean',
            'long_term_guest_snapshot' => 'boolean',
            'short_term_guest_snapshot' => 'boolean',
            'smokes_snapshot' => 'boolean',
            'prefers_quiet_snapshot' => 'boolean',
            'roommate_rating_average_snapshot' => 'decimal:2',
            'can_show_before_booking' => 'boolean',
            'can_show_after_booking' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleOccupants(Builder $query): Builder
    {
        return $query->whereIn('status', self::visibleStatuses());
    }

    public function scopeOverlapping(Builder $query, string $checkIn, string $checkOut): Builder
    {
        $checkInDateTime = CarbonImmutable::parse($checkIn)->startOfDay()->toDateTimeString();
        $checkOutDateTime = CarbonImmutable::parse($checkOut)->startOfDay()->toDateTimeString();

        return $query
            ->where('check_in_date', '<', $checkOutDateTime)
            ->where('check_out_date', '>', $checkInDateTime);
    }

    /**
     * @return list<string>
     */
    public static function visibleStatuses(): array
    {
        return [
            self::STATUS_UPCOMING,
            self::STATUS_CURRENT,
            self::STATUS_CHECKED_IN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_LEAVING_SOON,
        ];
    }
}
