<?php

namespace App\Models;

use Database\Factories\BookingCheckOutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingCheckOut extends Model
{
    /** @use HasFactory<BookingCheckOutFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'check_out_date',
        'planned_check_out_time',
        'actual_check_out_at',
        'check_out_method',
        'keys_returned',
        'keys_returned_count',
        'access_card_returned',
        'electronic_key_disabled',
        'locker_emptied',
        'locker_key_returned',
        'personal_items_taken',
        'bedding_returned',
        'towel_returned',
        'sleeping_place_free',
        'room_checked',
        'sleeping_place_checked',
        'has_damage',
        'has_extra_dirty',
        'has_forgotten_items',
        'needs_deposit_deduction',
        'deposit_deduction_amount',
        'deposit_deduction_reason',
        'after_place_photo_path',
        'after_room_photo_path',
        'damage_photo_paths_json',
        'guest_confirmed_at',
        'host_confirmed_at',
        'status',
        'problem_status',
        'last_reminder_sent_at',
    ];

    protected $attributes = [
        'status' => 'not_started',
        'keys_returned' => false,
        'locker_emptied' => false,
        'personal_items_taken' => false,
        'bedding_returned' => false,
        'towel_returned' => false,
        'sleeping_place_free' => false,
        'room_checked' => false,
        'sleeping_place_checked' => false,
        'has_damage' => false,
        'has_extra_dirty' => false,
        'has_forgotten_items' => false,
        'needs_deposit_deduction' => false,
    ];

    /**
     * Defines how Laravel converts stored Booking Check Out attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_out_date' => 'date:Y-m-d',
            'actual_check_out_at' => 'datetime',
            'keys_returned' => 'boolean',
            'keys_returned_count' => 'integer',
            'access_card_returned' => 'boolean',
            'electronic_key_disabled' => 'boolean',
            'locker_emptied' => 'boolean',
            'locker_key_returned' => 'boolean',
            'personal_items_taken' => 'boolean',
            'bedding_returned' => 'boolean',
            'towel_returned' => 'boolean',
            'sleeping_place_free' => 'boolean',
            'room_checked' => 'boolean',
            'sleeping_place_checked' => 'boolean',
            'has_damage' => 'boolean',
            'has_extra_dirty' => 'boolean',
            'has_forgotten_items' => 'boolean',
            'needs_deposit_deduction' => 'boolean',
            'deposit_deduction_amount' => 'decimal:2',
            'damage_photo_paths_json' => 'array',
            'guest_confirmed_at' => 'datetime',
            'host_confirmed_at' => 'datetime',
            'last_reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check Out to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Check Out to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Check Out to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Booking Check Out to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Booking Check Out to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Booking Check Out to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists related Booking Check Out Checklist Item records for this Booking Check Out.
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(BookingCheckOutChecklistItem::class);
    }

    /**
     * Lists related Booking Check Out Issue Report records for this Booking Check Out.
     */
    public function issueReports(): HasMany
    {
        return $this->hasMany(BookingCheckOutIssueReport::class);
    }

    /**
     * Lists related Booking Forgotten Item records for this Booking Check Out.
     */
    public function forgottenItems(): HasMany
    {
        return $this->hasMany(BookingForgottenItem::class);
    }

    /**
     * Fetches the single Booking Deposit Decision record used by this Booking Check Out.
     */
    public function depositDecision(): HasOne
    {
        return $this->hasOne(BookingDepositDecision::class);
    }

    /**
     * Lists related Host Inspection Task records for this Booking Check Out.
     */
    public function inspectionTasks(): HasMany
    {
        return $this->hasMany(HostInspectionTask::class);
    }
}
