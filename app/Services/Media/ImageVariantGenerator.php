<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageVariantGenerator
{
    /**
     * @return array{path:string,thumb_path:string,mobile_path:string,full_path:string,width:int|null,height:int|null,mime:string|null,size:int|null}
     */
    public function generate(UploadedFile $file, string $directory, string $baseName, string $disk = 'public'): array
    {
        $contents = $this->readUpload($file);
        [$width, $height, $sourceMime] = $this->imageMetadata($contents, $file);
        $source = $this->hasMemoryForImageDecode($width, $height, strlen($contents)) ? $this->sourceImage($contents) : false;

        if (! $source) {
            $extension = $this->extension($file, $sourceMime);
            $path = $directory.'/'.$baseName.'-full.'.$extension;
            $this->put($disk, $path, $contents);

            return [
                'path' => $path,
                'thumb_path' => $path,
                'mobile_path' => $path,
                'full_path' => $path,
                'width' => $width,
                'height' => $height,
                'mime' => $sourceMime,
                'size' => $file->getSize(),
            ];
        }

        $fullPath = $directory.'/'.$baseName.'-full.webp';
        $mobilePath = $directory.'/'.$baseName.'-mobile.webp';
        $thumbPath = $directory.'/'.$baseName.'-thumb.webp';

        try {
            $this->writeWebpVariant($source, $contents, $fullPath, 1600, 86, $disk);
            $this->writeWebpVariant($source, $contents, $mobilePath, 720, 82, $disk);
            $this->writeWebpVariant($source, $contents, $thumbPath, 320, 78, $disk);
        } finally {
            $this->destroyImage($source);
        }

        return [
            'path' => $fullPath,
            'thumb_path' => $thumbPath,
            'mobile_path' => $mobilePath,
            'full_path' => $fullPath,
            'width' => $width,
            'height' => $height,
            'mime' => 'image/webp',
            'size' => $this->storedSize($disk, $fullPath, $file->getSize()),
        ];
    }

    private function readUpload(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        if (! is_string($path) || ! is_readable($path)) {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        return $contents;
    }

    /**
     * @return array{0:int|null,1:int|null,2:string|null}
     */
    private function imageMetadata(string $contents, UploadedFile $file): array
    {
        $size = @getimagesizefromstring($contents);

        if (! is_array($size)) {
            return [null, null, $file->getMimeType()];
        }

        return [
            (int) ($size[0] ?? 0) ?: null,
            (int) ($size[1] ?? 0) ?: null,
            (string) ($size['mime'] ?? $file->getMimeType()),
        ];
    }

    private function sourceImage(string $contents): mixed
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return false;
        }

        return @imagecreatefromstring($contents);
    }

    private function writeWebpVariant(mixed $source, string $originalContents, string $path, int $maxSize, int $quality, string $disk): void
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min($maxSize / max(1, $sourceWidth), $maxSize / max(1, $sourceHeight), 1);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));

        if (! $this->hasMemoryForResize($targetWidth, $targetHeight)) {
            $this->put($disk, $path, $originalContents);

            return;
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        try {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefill($target, 0, 0, $transparent);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

            ob_start();
            $written = imagewebp($target, null, $quality);
            $contents = (string) ob_get_clean();
        } finally {
            $this->destroyImage($target);
        }

        if (! $written || $contents === '') {
            throw new RuntimeException('The uploaded image variant could not be generated.');
        }

        $this->put($disk, $path, $contents);
    }

    private function extension(UploadedFile $file, ?string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => $file->extension() ?: 'jpg',
        };
    }

    private function put(string $disk, string $path, string $contents): void
    {
        if (Storage::disk($disk)->put($path, $contents) === false) {
            throw new RuntimeException('The uploaded image could not be stored.');
        }
    }

    private function storedSize(string $disk, string $path, ?int $fallback): ?int
    {
        try {
            return Storage::disk($disk)->size($path);
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function hasMemoryForImageDecode(?int $width, ?int $height, int $contentsBytes): bool
    {
        if (! $width || ! $height) {
            return true;
        }

        return $this->hasAvailableMemory(($width * $height * 8) + $contentsBytes + (32 * 1024 * 1024));
    }

    private function hasMemoryForResize(int $width, int $height): bool
    {
        return $this->hasAvailableMemory(($width * $height * 8) + (32 * 1024 * 1024));
    }

    private function hasAvailableMemory(int $estimatedBytes): bool
    {
        $limit = $this->memoryLimitBytes();

        if ($limit <= 0) {
            return true;
        }

        gc_collect_cycles();

        return ($limit - memory_get_usage(false)) > $estimatedBytes;
    }

    private function memoryLimitBytes(): int
    {
        $value = trim((string) ini_get('memory_limit'));

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower($value[-1]);
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function destroyImage(mixed $image): void
    {
        if (is_resource($image) || $image instanceof \GdImage) {
            imagedestroy($image);
        }
    }
}
