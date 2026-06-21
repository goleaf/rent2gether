<?php

namespace App\Models;

use Database\Factories\SleepingPlaceAvailabilityStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceAvailabilityStatusLog extends Model
{
    /** @use HasFactory<SleepingPlaceAvailabilityStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'date',
        'old_status',
        'new_status',
        'source_type',
        'source_id',
        'user_id',
        'note',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Availability Status Log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    /**
     * Links this status log entry to the Sleeping Place it describes.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this status log entry to the User who caused it when known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
