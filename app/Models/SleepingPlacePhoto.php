<?php

namespace App\Models;

use Database\Factories\SleepingPlacePhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlacePhoto extends Model
{
    /** @use HasFactory<SleepingPlacePhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'uploaded_by_user_id',
        'media_item_id',
        'disk',
        'path',
        'thumbnail_path',
        'caption',
        'sort_order',
        'is_primary',
        'is_main',
        'status',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
            'is_main' => 'boolean',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
