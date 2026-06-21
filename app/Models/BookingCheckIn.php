<?php

namespace App\Models;

use Database\Factories\BookingCheckInFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'check_in_window',
        'actual_arrival_at',
        'actual_check_in_at',
        'check_in_method',
        'met_by_type',
        'met_by_name',
        'guest_on_the_way_at',
        'guest_arrived_at',
        'host_notified_guest_arrived_at',
        'instructions_available_at',
        'instructions_shown_at',
        'address_shown_at',
        'access_details_shown_at',
        'host_contact_shown_at',
        'representative_contact_shown_at',
        'keys_handed_over',
        'keys_count',
        'door_code_shared',
        'intercom_code_shared',
        'key_safe_code_shared',
        'door_code_provided',
        'intercom_code_provided',
        'key_safe_code_provided',
        'room_shown',
        'sleeping_place_shown',
        'rules_explained',
        'kitchen_rules_explained',
        'bathroom_rules_explained',
        'quiet_rules_explained',
        'bedding_given',
        'towel_given',
        'locker_given',
        'bedding_issued',
        'towel_issued',
        'locker_assigned',
        'locker_key_given',
        'before_place_photo_path',
        'before_room_photo_path',
        'guest_confirmed_at',
        'host_confirmed_at',
        'checked_in_at',
        'has_problem',
        'problem_reported_at',
        'problem_summary',
        'problem_status',
        'status',
        'last_reminder_sent_at',
        'closed_at',
    ];

    /**
     * Defines how Laravel converts stored Booking Check In attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'actual_arrival_at' => 'datetime',
            'actual_check_in_at' => 'datetime',
            'guest_on_the_way_at' => 'datetime',
            'guest_arrived_at' => 'datetime',
            'host_notified_guest_arrived_at' => 'datetime',
            'instructions_available_at' => 'datetime',
            'instructions_shown_at' => 'datetime',
            'address_shown_at' => 'datetime',
            'access_details_shown_at' => 'datetime',
            'host_contact_shown_at' => 'datetime',
            'representative_contact_shown_at' => 'datetime',
            'keys_handed_over' => 'boolean',
            'keys_count' => 'integer',
            'door_code_shared' => 'boolean',
            'intercom_code_shared' => 'boolean',
            'key_safe_code_shared' => 'boolean',
            'door_code_provided' => 'boolean',
            'intercom_code_provided' => 'boolean',
            'key_safe_code_provided' => 'boolean',
            'room_shown' => 'boolean',
            'sleeping_place_shown' => 'boolean',
            'rules_explained' => 'boolean',
            'kitchen_rules_explained' => 'boolean',
            'bathroom_rules_explained' => 'boolean',
            'quiet_rules_explained' => 'boolean',
            'bedding_given' => 'boolean',
            'towel_given' => 'boolean',
            'locker_given' => 'boolean',
            'bedding_issued' => 'boolean',
            'towel_issued' => 'boolean',
            'locker_assigned' => 'boolean',
            'locker_key_given' => 'boolean',
            'guest_confirmed_at' => 'datetime',
            'host_confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'has_problem' => 'boolean',
            'problem_reported_at' => 'datetime',
            'last_reminder_sent_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check In to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Check In to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Check In to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Booking Check In to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Booking Check In to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Booking Check In to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists related Booking Check In Checklist Item records for this Booking Check In.
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(BookingCheckInChecklistItem::class);
    }

    /**
     * Lists related Booking Check In Problem Report records for this Booking Check In.
     */
    public function problemReports(): HasMany
    {
        return $this->hasMany(BookingCheckInProblemReport::class);
    }

    /**
     * Lists related Booking Check In Alert records for this Booking Check In.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(BookingCheckInAlert::class);
    }

    /**
     * Fetches the immutable instruction snapshot created for this check-in.
     */
    public function instruction(): HasOne
    {
        return $this->hasOne(BookingCheckInInstruction::class);
    }

    /**
     * Lists sensitive access disclosures shown during this check-in.
     */
    public function accessDisclosures(): HasMany
    {
        return $this->hasMany(BookingCheckInAccessDisclosure::class);
    }

    /**
     * Lists point-ten checklist steps for this check-in process.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(BookingCheckInStep::class);
    }

    /**
     * Lists photos and future media attached to this check-in.
     */
    public function media(): HasMany
    {
        return $this->hasMany(BookingCheckInMedia::class);
    }

    /**
     * Lists point-ten problem reports attached to this check-in.
     */
    public function problems(): HasMany
    {
        return $this->hasMany(BookingCheckInProblem::class);
    }

    /**
     * Lists status transition logs for this check-in process.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingCheckInStatusLog::class);
    }

    /**
     * Lists no-show cases attached to this check-in attempt.
     */
    public function noShows(): HasMany
    {
        return $this->hasMany(BookingNoShow::class);
    }

    /**
     * Lists host-unresponsive cases attached to this check-in attempt.
     */
    public function hostUnresponsiveCases(): HasMany
    {
        return $this->hasMany(BookingHostUnresponsiveCase::class);
    }
}
