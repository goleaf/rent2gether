<?php

namespace App\Models;

use Database\Factories\PropertyPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPhoto extends Model
{
    /** @use HasFactory<PropertyPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
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

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
