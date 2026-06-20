<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCreationBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCreationBatch extends Model
{
    /** @use HasFactory<SleepingPlaceCreationBatchFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'batch_name',
        'places_count',
        'template_json',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Creation Batch attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'places_count' => 'integer',
            'template_json' => 'array',
        ];
    }

    /**
     * Links this Sleeping Place Creation Batch to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Sleeping Place Creation Batch to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Sleeping Place Creation Batch to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
