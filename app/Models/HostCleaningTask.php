<?php

namespace App\Models;

use Database\Factories\HostCleaningTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostCleaningTask extends Model
{
    /** @use HasFactory<HostCleaningTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'booking_check_out_id',
        'cleaning_type',
        'status',
        'priority',
        'scheduled_date',
        'scheduled_time',
        'due_at',
        'started_at',
        'reason',
        'note',
        'host_note',
        'cleaner_comment',
        'assigned_to_type',
        'assigned_to_user_id',
        'assigned_person_name',
        'assigned_person_contact',
        'before_photos_required',
        'after_photos_required',
        'has_before_photos',
        'has_after_photos',
        'has_damage_found',
        'has_forgotten_items',
        'has_extra_dirty',
        'needs_repair',
        'needs_repeat_cleaning',
        'place_ready_after_cleaning',
        'completed_at',
        'cancelled_at',
    ];

    protected $attributes = [
        'status' => 'planned',
        'cleaning_type' => 'after_check_out',
        'priority' => 'normal',
        'before_photos_required' => false,
        'after_photos_required' => true,
        'has_before_photos' => false,
        'has_after_photos' => false,
        'has_damage_found' => false,
        'has_forgotten_items' => false,
        'has_extra_dirty' => false,
        'needs_repair' => false,
        'needs_repeat_cleaning' => false,
        'place_ready_after_cleaning' => false,
    ];

    /**
     * Defines how Laravel converts stored Host Cleaning Task attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'due_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'before_photos_required' => 'boolean',
            'after_photos_required' => 'boolean',
            'has_before_photos' => 'boolean',
            'has_after_photos' => 'boolean',
            'has_damage_found' => 'boolean',
            'has_forgotten_items' => 'boolean',
            'has_extra_dirty' => 'boolean',
            'needs_repair' => 'boolean',
            'needs_repeat_cleaning' => 'boolean',
            'place_ready_after_cleaning' => 'boolean',
        ];
    }

    /**
     * Links this Host Cleaning Task to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Host Cleaning Task to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Links this Host Cleaning Task to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Host Cleaning Task to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Host Cleaning Task to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Host Cleaning Task to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Host Cleaning Task to the Booking Check Out record used by its booking check out relation.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class);
    }

    /**
     * Links this Host Cleaning Task to the User record used by its assigned to relation.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Lists related Host Cleaning Task Item records for this Host Cleaning Task.
     */
    public function items(): HasMany
    {
        return $this->hasMany(HostCleaningTaskItem::class);
    }

    /**
     * Lists related Host Cleaning Task Photo records for this Host Cleaning Task.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(HostCleaningTaskPhoto::class);
    }

    /**
     * Lists related Host Cleaning Finding records for this Host Cleaning Task.
     */
    public function findings(): HasMany
    {
        return $this->hasMany(HostCleaningFinding::class);
    }

    /**
     * Lists related Host Calendar Event records for this Host Cleaning Task.
     */
    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class, 'cleaning_task_id');
    }
}
