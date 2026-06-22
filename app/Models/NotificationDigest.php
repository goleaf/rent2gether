<?php

namespace App\Models;

use Database\Factories\NotificationDigestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationDigest extends Model
{
    /** @use HasFactory<NotificationDigestFactory> */
    use HasFactory;

    protected $fillable = [
        'digest_number',
        'user_id',
        'digest_type',
        'status',
        'period_start',
        'period_end',
        'notification_count',
        'urgent_count',
        'important_count',
        'title_translation_key',
        'body_translation_key',
        'translation_params_json',
        'sent_at',
        'read_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'translation_params_json' => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this digest to its user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lists notifications included in this digest.
     */
    public function items(): HasMany
    {
        return $this->hasMany(NotificationDigestItem::class);
    }
}
