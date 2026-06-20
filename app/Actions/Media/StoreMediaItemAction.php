<?php

namespace App\Actions\Media;

use App\Models\MediaItem;
use App\Models\User;
use App\Services\Media\ImageVariantGenerator;
use App\Services\Media\MediaOwnerResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreMediaItemAction
{
    public function __construct(
        private readonly ImageVariantGenerator $variants,
        private readonly MediaOwnerResolver $owners,
    ) {}

    public function handle(
        Model $owner,
        UploadedFile $file,
        User $user,
        string $collection = 'gallery',
        ?string $captionEn = null,
        ?string $captionRu = null,
        bool $makePrimary = false,
    ): MediaItem {
        $directory = $this->owners->directoryFor($owner, $collection);
        $variantPaths = $this->variants->generate($file, $directory, (string) Str::uuid());
        $sortOrder = (int) $owner->mediaItems()
            ->where('collection', $collection)
            ->max('sort_order') + 1;
        $shouldBePrimary = $makePrimary || ! $owner->mediaItems()
            ->where('collection', $collection)
            ->active()
            ->exists();

        if ($shouldBePrimary) {
            $owner->mediaItems()
                ->where('collection', $collection)
                ->update(['is_primary' => false, 'is_cover' => false]);
        }

        return $owner->mediaItems()->create([
            'owner_type' => $owner::class,
            'owner_id' => $owner->getKey(),
            'owner_user_id' => $user->id,
            'collection' => $collection,
            'disk' => 'public',
            'path' => $variantPaths['path'],
            'thumbnail_path' => $variantPaths['thumb_path'],
            'thumb_path' => $variantPaths['thumb_path'],
            'mobile_path' => $variantPaths['mobile_path'],
            'full_path' => $variantPaths['full_path'],
            'original_filename' => $file->getClientOriginalName(),
            'mime' => $variantPaths['mime'],
            'mime_type' => $variantPaths['mime'],
            'size' => $variantPaths['size'],
            'size_bytes' => $variantPaths['size'],
            'width' => $variantPaths['width'],
            'height' => $variantPaths['height'],
            'caption_en' => $captionEn,
            'caption_ru' => $captionRu,
            'alt_text' => $captionEn ?: $captionRu,
            'sort_order' => $sortOrder,
            'is_primary' => $shouldBePrimary,
            'is_cover' => $shouldBePrimary,
            'status' => 'active',
        ]);
    }
}
