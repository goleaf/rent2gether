<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'bed_id', 'desired_check_in', 'desired_check_out', 'max_price',
    'ready_to_book', 'auto_request', 'notified', 'notified_at', 'status',
])]
class WaitlistEntry extends Model
{
    protected $casts = [
        'desired_check_in' => 'date',
        'desired_check_out' => 'date',
        'max_price' => 'decimal:2',
        'ready_to_book' => 'boolean',
        'auto_request' => 'boolean',
        'notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    /**
     * Links this Waitlist Entry to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Waitlist Entry to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }
}
