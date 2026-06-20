<?php

namespace App\Models;

use Database\Factories\RoomTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTemplate extends Model
{
    /** @use HasFactory<RoomTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'room_type',
        'template_json',
        'is_default',
    ];

    /**
     * Defines how Laravel converts stored Room Template attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'template_json' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Links this Room Template to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
