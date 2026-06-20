<?php

namespace App\Models;

use Database\Factories\RoomPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomPhoto extends Model
{
    /** @use HasFactory<RoomPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
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

    /**
     * Defines how Laravel converts stored Room Photo attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
            'is_main' => 'boolean',
        ];
    }

    /**
     * Links this Room Photo to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Room Photo to the User record used by its uploaded by relation.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
