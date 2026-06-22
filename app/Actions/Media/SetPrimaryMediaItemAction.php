<?php

namespace App\Actions\Media;

use App\Models\MediaItem;
use App\Models\PropertyPhoto;
use App\Models\RoomPhoto;
use App\Models\SleepingPlacePhoto;

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

        foreach ([PropertyPhoto::class, RoomPhoto::class, SleepingPlacePhoto::class] as $photoModel) {
            $photoModel::query()
                ->whereIn('media_item_id', $owner->mediaItems()->where('collection', $mediaItem->collection)->select('id'))
                ->update(['is_primary' => false, 'is_main' => false]);

            $photoModel::query()
                ->where('media_item_id', $mediaItem->id)
                ->update(['is_primary' => true, 'is_main' => true]);
        }
    }
}
