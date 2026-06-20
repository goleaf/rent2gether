<?php

namespace App\Models;

use Database\Factories\GuestHintImpressionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHintImpression extends Model
{
    /** @use HasFactory<GuestHintImpressionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sleeping_place_id',
        'hint_key',
        'context',
        'shown_at',
        'clicked_at',
        'dismissed_at',
    ];

    /**
     * Defines how Laravel converts stored Guest Hint Impression attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'shown_at' => 'datetime',
            'clicked_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    /**
     * Links this Guest Hint Impression to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Guest Hint Impression to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
