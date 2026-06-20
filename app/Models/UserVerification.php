<?php

namespace App\Models;

use Database\Factories\UserVerificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerification extends Model
{
    /** @use HasFactory<UserVerificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verification_type',
        'status',
        'provider',
        'verified_at',
        'expires_at',
        'rejection_reason',
        'metadata_json',
    ];

    protected $hidden = [
        'metadata_json',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
