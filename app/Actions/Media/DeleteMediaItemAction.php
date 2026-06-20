<?php

namespace App\Actions\Media;

use App\Models\MediaItem;
use Illuminate\Support\Facades\Storage;

class DeleteMediaItemAction
{
    public function handle(MediaItem $mediaItem): void
    {
        $wasPrimary = (bool) ($mediaItem->is_primary || $mediaItem->is_cover);
        $owner = $mediaItem->mediable;
        $collection = $mediaItem->collection;

        collect([
            $mediaItem->path,
            $mediaItem->thumb_path,
            $mediaItem->thumbnail_path,
            $mediaItem->mobile_path,
            $mediaItem->full_path,
        ])->filter()
            ->unique()
            ->each(fn (string $path): bool => Storage::disk($mediaItem->disk ?: 'public')->delete($path));

        $mediaItem->delete();

        if ($wasPrimary && $owner) {
            $next = $owner->mediaItems()
                ->where('collection', $collection)
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            $next?->update(['is_primary' => true, 'is_cover' => true]);
        }
    }
}
