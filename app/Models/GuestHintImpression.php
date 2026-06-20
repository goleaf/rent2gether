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

    protected function casts(): array
    {
        return [
            'shown_at' => 'datetime',
            'clicked_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
