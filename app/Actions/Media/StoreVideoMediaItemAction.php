<?php

namespace App\Actions\Media;

use App\Models\MediaItem;
use App\Models\User;
use App\Services\Localization\SupportedContentLocales;
use App\Services\Media\MediaOwnerResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreVideoMediaItemAction
{
    public function __construct(
        private readonly MediaOwnerResolver $owners,
        private readonly SupportedContentLocales $locales,
    ) {}

    /**
     * Stores a lightweight listing video without image-variant processing.
     *
     * @param  array<string, mixed>  $captions
     */
    public function handle(
        Model $owner,
        UploadedFile $file,
        User $user,
        string $collection = 'video',
        array $captions = [],
    ): MediaItem {
        $directory = $this->owners->directoryFor($owner, $collection);
        $extension = $file->extension() ?: $file->guessExtension() ?: 'mp4';
        $filename = (string) Str::uuid().'.'.$extension;
        $path = Storage::disk('public')->putFileAs($directory, $file, $filename);

        if ($path === false) {
            throw new RuntimeException('Unable to store listing video.');
        }

        $sortOrder = (int) $owner->mediaItems()
            ->where('collection', $collection)
            ->max('sort_order') + 1;
        $cleanCaptions = $this->cleanCaptions($captions);

        /** @var MediaItem $mediaItem */
        $mediaItem = $owner->mediaItems()->create([
            'owner_type' => $owner::class,
            'owner_id' => $owner->getKey(),
            'owner_user_id' => $user->id,
            'collection' => $collection,
            'disk' => 'public',
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'size_bytes' => $file->getSize(),
            'alt_text' => collect($cleanCaptions)->first(),
            'sort_order' => $sortOrder,
            'is_primary' => false,
            'is_cover' => false,
            'status' => 'active',
        ]);

        foreach ($cleanCaptions as $locale => $caption) {
            $mediaItem->translations()->create([
                'locale' => $locale,
                'caption' => $caption,
            ]);
        }

        return $mediaItem->load('translations');
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
