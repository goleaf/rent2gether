<?php

namespace App\Models;

use Database\Factories\NotificationDigestItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDigestItem extends Model
{
    /** @use HasFactory<NotificationDigestItemFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_digest_id',
        'notification_id',
        'sort_order',
    ];

    /**
     * Links this row to its digest.
     */
    public function digest(): BelongsTo
    {
        return $this->belongsTo(NotificationDigest::class, 'notification_digest_id');
    }

    /**
     * Links this row to its notification.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
