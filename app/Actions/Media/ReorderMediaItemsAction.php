<?php

namespace App\Actions\Media;

use App\Models\MediaItem;

class ReorderMediaItemsAction
{
    public function move(MediaItem $mediaItem, string $direction): void
    {
        $owner = $mediaItem->mediable;

        if (! $owner) {
            return;
        }

        $items = $owner->mediaItems()
            ->where('collection', $mediaItem->collection)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'sort_order']);
        $index = $items->search(fn (MediaItem $item): bool => (int) $item->id === (int) $mediaItem->id);

        if ($index === false) {
            return;
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! $items->has($swapIndex)) {
            return;
        }

        $swap = $items->get($swapIndex);
        $currentOrder = $mediaItem->sort_order;

        $mediaItem->update(['sort_order' => $swap->sort_order]);
        $swap->update(['sort_order' => $currentOrder]);
    }
}
