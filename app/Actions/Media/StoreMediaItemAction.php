<?php

namespace App\Actions\Media;

use App\Models\MediaItem;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\SleepingPlace;
use App\Models\SleepingPlacePhoto;
use App\Models\User;
use App\Services\Localization\SupportedContentLocales;
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
        private readonly SupportedContentLocales $locales,
    ) {}

    public function handle(
        Model $owner,
        UploadedFile $file,
        User $user,
        string $collection = 'gallery',
        array $captions = [],
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

        $cleanCaptions = $this->cleanCaptions($captions);

        /** @var MediaItem $mediaItem */
        $mediaItem = $owner->mediaItems()->create([
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
            'alt_text' => collect($cleanCaptions)->first(),
            'sort_order' => $sortOrder,
            'is_primary' => $shouldBePrimary,
            'is_cover' => $shouldBePrimary,
            'status' => 'active',
        ]);

        foreach ($cleanCaptions as $locale => $caption) {
            $mediaItem->translations()->create([
                'locale' => $locale,
                'caption' => $caption,
            ]);
        }

        $this->syncListingPhoto($owner, $mediaItem, $user, $cleanCaptions);

        return $mediaItem->load('translations');
    }

    /**
     * Mirrors uploaded listing media into legacy photo tables used by readiness checks.
     *
     * @param  array<string, string>  $captions
     */
    private function syncListingPhoto(Model $owner, MediaItem $mediaItem, User $user, array $captions): void
    {
        $photoModel = match (true) {
            $owner instanceof Property => PropertyPhoto::class,
            $owner instanceof Room => RoomPhoto::class,
            $owner instanceof SleepingPlace => SleepingPlacePhoto::class,
            default => null,
        };

        $ownerColumn = match (true) {
            $owner instanceof Property => 'property_id',
            $owner instanceof Room => 'room_id',
            $owner instanceof SleepingPlace => 'sleeping_place_id',
            default => null,
        };

        if ($photoModel === null || $ownerColumn === null) {
            return;
        }

        $photoModel::query()->updateOrCreate(
            ['media_item_id' => $mediaItem->id],
            [
                $ownerColumn => $owner->id,
                'uploaded_by_user_id' => $user->id,
                'disk' => $mediaItem->disk ?: 'public',
                'path' => $mediaItem->path,
                'thumbnail_path' => $mediaItem->thumb_path ?: $mediaItem->thumbnail_path,
                'caption' => collect($captions)->first(),
                'sort_order' => $mediaItem->sort_order,
                'is_primary' => (bool) $mediaItem->is_primary,
                'is_main' => (bool) ($mediaItem->is_primary || $mediaItem->is_cover),
                'status' => $mediaItem->status ?: 'active',
                'visibility' => 'public',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $captions
     * @return array<string, string>
     */
    private function cleanCaptions(array $captions): array
    {
        return collect($this->locales->locales())
            ->mapWithKeys(function (string $locale) use ($captions): array {
                $caption = trim((string) ($captions[$locale] ?? ''));

                return $caption === '' ? [] : [$locale => $caption];
            })
            ->all();
    }
}
