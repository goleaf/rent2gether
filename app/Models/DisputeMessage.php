<?php

namespace App\Models;

use Database\Factories\DisputeMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeMessage extends Model
{
    /** @use HasFactory<DisputeMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'dispute_case_id',
        'user_id',
        'message_type',
        'message',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'guest_and_host',
    ];

    public function disputeCase(): BelongsTo
    {
        return $this->belongsTo(DisputeCase::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
