<?php

namespace App\Services\Media;

use App\Models\MediaItem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoMediaFileService
{
    private const FALLBACK_JPEG = '/9j/4AAQSkZJRgABAQEAYABgAAD//gA+Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2NjIpLCBkZWZhdWx0IHF1YWxpdHkK/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgAAQABAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A+f6KKKAP/9k=';

    private const FALLBACK_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAADElEQVQImWNgYGAAAAAEAAGjChXjAAAAAElFTkSuQmCC';

    private const FALLBACK_WEBP = 'UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/v02aAA=';

    /**
     * @var array<string, string>
     */
    private array $imageCache = [];

    private bool $storageLinkChecked = false;

    public function ensurePublicStorageLink(): void
    {
        if ($this->storageLinkChecked) {
            return;
        }

        $this->storageLinkChecked = true;
        $links = config('filesystems.links', []);

        if (! is_array($links)) {
            return;
        }

        foreach ($links as $link => $target) {
            if (! is_string($link) || ! is_string($target)) {
                continue;
            }

            if (file_exists($link) || is_link($link)) {
                continue;
            }

            if (! is_dir($target)) {
                mkdir($target, 0755, true);
            }

            $parent = dirname($link);

            if (! is_dir($parent)) {
                mkdir($parent, 0755, true);
            }

            @symlink($target, $link);
        }
    }

    public function resetDemoDirectories(): void
    {
        Storage::disk('public')->deleteDirectory('demo-media');
        Storage::disk('public')->deleteDirectory('bulk-demo');
    }

    public function ensureForMediaItem(MediaItem $mediaItem, ?string $label = null): void
    {
        $this->ensurePublicStorageLink();

        foreach ($this->imagePaths($mediaItem) as $path => $dimensions) {
            $this->ensureImageFile(
                disk: $mediaItem->disk ?: 'public',
                path: $path,
                label: $label ?: $mediaItem->localizedCaption('en') ?: 'Rent2Gether demo',
                width: $dimensions['width'],
                height: $dimensions['height'],
            );
        }
    }

    /**
     * @param  iterable<MediaItem>  $mediaItems
     */
    public function ensureForMediaItems(iterable $mediaItems): void
    {
        $this->ensurePublicStorageLink();

        foreach ($mediaItems as $mediaItem) {
            $this->ensureForMediaItem($mediaItem);
        }
    }

    /**
     * @return array<string, array{width:int,height:int}>
     */
    private function imagePaths(MediaItem $mediaItem): array
    {
        $width = max(1, (int) ($mediaItem->width ?: 1200));
        $height = max(1, (int) ($mediaItem->height ?: 800));
        $paths = [];

        foreach ([
            $mediaItem->path => ['width' => $width, 'height' => $height],
            $mediaItem->full_path => ['width' => $width, 'height' => $height],
            $mediaItem->mobile_path => ['width' => min(720, $width), 'height' => min(480, $height)],
            $mediaItem->thumb_path => ['width' => min(320, $width), 'height' => min(240, $height)],
            $mediaItem->thumbnail_path => ['width' => min(320, $width), 'height' => min(240, $height)],
        ] as $path => $dimensions) {
            if (is_string($path) && $path !== '') {
                $paths[$path] = $dimensions;
            }
        }

        return $paths;
    }

    private function ensureImageFile(string $disk, string $path, string $label, int $width, int $height): void
    {
        if (Storage::disk($disk)->exists($path)) {
            return;
        }

        $written = Storage::disk($disk)->put(
            $path,
            $this->imageContents($path, $label, max(1, min(1600, $width)), max(1, min(1200, $height))),
        );

        if ($written === false || ! Storage::disk($disk)->exists($path)) {
            throw new RuntimeException(sprintf('Unable to write demo media file [%s] to disk [%s].', $path, $disk));
        }
    }

    private function imageContents(string $path, string $label, int $width, int $height): string
    {
        $extension = $this->extension($path);
        $cacheKey = $extension.':'.$width.'x'.$height;

        return $this->imageCache[$cacheKey] ??= $this->renderImage($extension, $cacheKey, $label, $width, $height);
    }

    private function renderImage(string $extension, string $cacheKey, string $label, int $width, int $height): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return $this->fallbackContents($extension);
        }

        $image = imagecreatetruecolor($width, $height);
        $colors = $this->colors($cacheKey);
        $background = imagecolorallocate($image, $colors['background'][0], $colors['background'][1], $colors['background'][2]);
        $accent = imagecolorallocate($image, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2]);
        $soft = imagecolorallocate($image, 245, 245, 242);
        $text = imagecolorallocate($image, 38, 38, 38);

        imagefilledrectangle($image, 0, 0, $width, $height, $background);
        imagefilledrectangle($image, 0, 0, $width, max(12, (int) round($height * 0.16)), $accent);
        imagefilledrectangle($image, 0, max(0, $height - (int) round($height * 0.22)), $width, $height, $soft);

        if (function_exists('imagestring')) {
            imagestring($image, 5, 18, max(18, $height - 42), substr($label, 0, 44), $text);
        }

        ob_start();
        $written = match ($extension) {
            'png' => function_exists('imagepng') && imagepng($image),
            'webp' => function_exists('imagewebp') && imagewebp($image, null, 82),
            default => function_exists('imagejpeg') && imagejpeg($image, null, 84),
        };
        $contents = (string) ob_get_clean();

        return $written && $contents !== ''
            ? $contents
            : $this->fallbackContents($extension);
    }

    /**
     * @return array{background:array{0:int,1:int,2:int},accent:array{0:int,1:int,2:int}}
     */
    private function colors(string $path): array
    {
        $hash = crc32($path);

        return [
            'background' => [
                92 + (($hash >> 16) & 63),
                112 + (($hash >> 8) & 55),
                118 + ($hash & 47),
            ],
            'accent' => [
                38 + (($hash >> 12) & 63),
                58 + (($hash >> 4) & 63),
                72 + (($hash >> 20) & 63),
            ],
        ];
    }

    private function extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'jpg';
    }

    private function fallbackContents(string $extension): string
    {
        $encoded = match ($extension) {
            'png' => self::FALLBACK_PNG,
            'webp' => self::FALLBACK_WEBP,
            default => self::FALLBACK_JPEG,
        };

        $contents = base64_decode($encoded, true);

        if ($contents !== false) {
            return $contents;
        }

        return base64_decode(self::FALLBACK_JPEG, true) ?: '';
    }
}
