<?php

namespace App\Models;

use Database\Factories\FavoriteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'bed_id', 'collection', 'note', 'price_at_save',
    'notify_available', 'notify_price_drop',
])]
class Favorite extends Model
{
    /** @use HasFactory<FavoriteFactory> */
    use HasFactory;

    protected $casts = [
        'price_at_save' => 'decimal:2',
        'notify_available' => 'boolean',
        'notify_price_drop' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function priceChanged(): bool
    {
        return $this->price_at_save !== null
            && (float) $this->price_at_save !== (float) $this->bed->price_per_night;
    }
}
