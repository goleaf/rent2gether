<?php

namespace App\Models;

use Database\Factories\InventoryIssueMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryIssueMedia extends Model
{
    /** @use HasFactory<InventoryIssueMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_issue_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'guest_and_host',
    ];

    /**
     * Links this evidence file to its inventory issue.
     */
    public function inventoryIssue(): BelongsTo
    {
        return $this->belongsTo(InventoryIssue::class);
    }

    /**
     * Links this evidence file to its booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this evidence file to the uploader.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
