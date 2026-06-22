<?php

namespace App\Models;

use Database\Factories\NotificationDeviceTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDeviceToken extends Model
{
    /** @use HasFactory<NotificationDeviceTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'device_name',
        'token_hash',
        'token_encrypted',
        'active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * Links this future push token to its user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
