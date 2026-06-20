<?php

namespace App\Models;

use Database\Factories\SleepingPlaceStorageDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceStorageDetail extends Model
{
    /** @use HasFactory<SleepingPlaceStorageDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'has_shoe_space',
        'has_luggage_space',
        'has_backpack_space',
        'has_under_bed_storage',
        'has_under_bed_drawer',
        'has_personal_locker',
        'locker_has_lock',
        'lock_provided',
        'guest_should_bring_lock',
        'can_store_valuables',
        'can_store_documents',
        'can_store_laptop',
        'locker_size',
        'locker_width_cm',
        'locker_height_cm',
        'locker_depth_cm',
        'has_shared_storage_area',
        'can_leave_luggage_before_checkin',
        'can_leave_luggage_after_checkout',
        'storage_responsibility_note',
    ];

    protected function casts(): array
    {
        return [
            'has_shoe_space' => 'boolean',
            'has_luggage_space' => 'boolean',
            'has_backpack_space' => 'boolean',
            'has_under_bed_storage' => 'boolean',
            'has_under_bed_drawer' => 'boolean',
            'has_personal_locker' => 'boolean',
            'locker_has_lock' => 'boolean',
            'lock_provided' => 'boolean',
            'guest_should_bring_lock' => 'boolean',
            'can_store_valuables' => 'boolean',
            'can_store_documents' => 'boolean',
            'can_store_laptop' => 'boolean',
            'locker_width_cm' => 'integer',
            'locker_height_cm' => 'integer',
            'locker_depth_cm' => 'integer',
            'has_shared_storage_area' => 'boolean',
            'can_leave_luggage_before_checkin' => 'boolean',
            'can_leave_luggage_after_checkout' => 'boolean',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
