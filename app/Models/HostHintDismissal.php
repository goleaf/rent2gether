<?php

namespace App\Models;

use Database\Factories\HostHintDismissalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostHintDismissal extends Model
{
    /** @use HasFactory<HostHintDismissalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'hint_key',
        'context',
        'dismissed_until',
        'dismissed_at',
    ];

    /**
     * Defines how Laravel converts stored Host Hint Dismissal attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'dismissed_until' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    /**
     * Links this Host Hint Dismissal to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Host Hint Dismissal to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Host Hint Dismissal to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Host Hint Dismissal to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
