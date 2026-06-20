<?php

namespace App\Models;

use Database\Factories\BookingCheckInFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingCheckIn extends Model
{
    /** @use HasFactory<BookingCheckInFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'check_in_date',
        'planned_check_in_time',
        'planned_check_in_window',
        'actual_arrival_at',
        'actual_check_in_at',
        'check_in_method',
        'met_by_type',
        'met_by_name',
        'keys_handed_over',
        'keys_count',
        'door_code_shared',
        'intercom_code_shared',
        'key_safe_code_shared',
        'room_shown',
        'sleeping_place_shown',
        'rules_explained',
        'kitchen_rules_explained',
        'bathroom_rules_explained',
        'quiet_rules_explained',
        'bedding_given',
        'towel_given',
        'locker_given',
        'locker_key_given',
        'before_place_photo_path',
        'before_room_photo_path',
        'guest_confirmed_at',
        'host_confirmed_at',
        'has_problem',
        'problem_status',
        'status',
        'last_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'actual_arrival_at' => 'datetime',
            'actual_check_in_at' => 'datetime',
            'keys_handed_over' => 'boolean',
            'keys_count' => 'integer',
            'door_code_shared' => 'boolean',
            'intercom_code_shared' => 'boolean',
            'key_safe_code_shared' => 'boolean',
            'room_shown' => 'boolean',
            'sleeping_place_shown' => 'boolean',
            'rules_explained' => 'boolean',
            'kitchen_rules_explained' => 'boolean',
            'bathroom_rules_explained' => 'boolean',
            'quiet_rules_explained' => 'boolean',
            'bedding_given' => 'boolean',
            'towel_given' => 'boolean',
            'locker_given' => 'boolean',
            'locker_key_given' => 'boolean',
            'guest_confirmed_at' => 'datetime',
            'host_confirmed_at' => 'datetime',
            'has_problem' => 'boolean',
            'last_reminder_sent_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
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

    public function checklistItems(): HasMany
    {
        return $this->hasMany(BookingCheckInChecklistItem::class);
    }

    public function problemReports(): HasMany
    {
        return $this->hasMany(BookingCheckInProblemReport::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(BookingCheckInAlert::class);
    }
}
