<?php

namespace App\Models;

use Database\Factories\MediaItemTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaItemTranslation extends Model
{
    /** @use HasFactory<MediaItemTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'media_item_id',
        'locale',
        'caption',
    ];

    /**
     * Links this Media Item Translation to the Media Item record used by its media item relation.
     */
    public function mediaItem(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class);
    }
}
