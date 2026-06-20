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

    protected function casts(): array
    {
        return [
            'places_count' => 'integer',
            'template_json' => 'array',
        ];
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
}
