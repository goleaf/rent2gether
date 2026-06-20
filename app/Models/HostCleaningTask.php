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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingCheckOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HostCleaningTaskItem::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(HostCleaningTaskPhoto::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(HostCleaningFinding::class);
    }

    public function hostCalendarEvents(): HasMany
    {
        return $this->hasMany(HostCalendarEvent::class, 'cleaning_task_id');
    }
}
