<?php

namespace App\Models;

use Database\Factories\RoomTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTranslation extends Model
{
    /** @use HasFactory<RoomTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'locale',
        'title',
        'summary',
        'description',
        'notes',
        'sleeping_arrangement',
        'privacy_notes',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
