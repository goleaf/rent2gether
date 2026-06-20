<?php

namespace App\Models;

use Database\Factories\RoomLayoutDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomLayoutDetail extends Model
{
    /** @use HasFactory<RoomLayoutDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'area',
        'length_meters',
        'width_meters',
        'ceiling_height_meters',
        'windows_count',
        'window_size',
        'window_view',
        'windows_face_yard',
        'windows_face_street',
        'windows_face_quiet_side',
        'windows_face_noisy_road',
        'cardinal_direction',
        'has_balcony',
        'balcony_accessible',
        'has_free_passage_space',
        'narrow_passages',
        'has_many_free_space',
        'has_little_free_space',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'length_meters' => 'decimal:2',
            'width_meters' => 'decimal:2',
            'ceiling_height_meters' => 'decimal:2',
            'windows_count' => 'integer',
            'windows_face_yard' => 'boolean',
            'windows_face_street' => 'boolean',
            'windows_face_quiet_side' => 'boolean',
            'windows_face_noisy_road' => 'boolean',
            'has_balcony' => 'boolean',
            'balcony_accessible' => 'boolean',
            'has_free_passage_space' => 'boolean',
            'narrow_passages' => 'boolean',
            'has_many_free_space' => 'boolean',
            'has_little_free_space' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
