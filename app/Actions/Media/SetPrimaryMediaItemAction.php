<?php

namespace App\Actions\Media;

use App\Models\MediaItem;

class SetPrimaryMediaItemAction
{
    public function handle(MediaItem $mediaItem): void
    {
        $owner = $mediaItem->mediable;

        if (! $owner) {
            return;
        }

        $owner->mediaItems()
            ->where('collection', $mediaItem->collection)
            ->update(['is_primary' => false, 'is_cover' => false]);

        $mediaItem->update(['is_primary' => true, 'is_cover' => true]);
    }
}
