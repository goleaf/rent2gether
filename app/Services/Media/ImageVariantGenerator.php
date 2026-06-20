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
        [$width, $height, $mime] = $this->imageMetadata($contents, $file);
        $size = $file->getSize();
        $source = $this->hasMemoryForImageDecode($width, $height) ? $this->sourceImage($contents) : false;

        if (! $source) {
            $extension = $this->extension($file, $mime);
            $path = $directory.'/'.$baseName.'-full.'.$extension;
            $this->put($disk, $path, $contents);

            return [
                'path' => $path,
                'thumb_path' => $path,
                'mobile_path' => $path,
                'full_path' => $path,
                'width' => $width,
                'height' => $height,
                'mime' => $mime,
                'size' => $size,
            ];
        }

        $fullPath = $directory.'/'.$baseName.'-full.jpg';
        $mobilePath = $directory.'/'.$baseName.'-mobile.jpg';
        $thumbPath = $directory.'/'.$baseName.'-thumb.jpg';

        try {
            $this->writeJpegVariant($source, $contents, $mime, $width, $height, $fullPath, 1600, 84, $disk);
            $this->writeJpegVariant($source, $contents, $mime, $width, $height, $mobilePath, 720, 80, $disk);
            $this->writeJpegVariant($source, $contents, $mime, $width, $height, $thumbPath, 320, 76, $disk);
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
            'mime' => $mime,
            'size' => $size,
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
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return false;
        }

        return @imagecreatefromstring($contents);
    }

    private function writeJpegVariant(mixed $source, string $originalContents, ?string $mime, ?int $width, ?int $height, string $path, int $maxSize, int $quality, string $disk): void
    {
        if ($this->canCopyOriginalJpeg($mime, $width, $height, $maxSize)) {
            $this->put($disk, $path, $originalContents);

            return;
        }

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
            $white = imagecolorallocate($target, 255, 255, 255);
            imagefill($target, 0, 0, $white);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

            ob_start();
            $written = imagejpeg($target, null, $quality);
            $contents = (string) ob_get_clean();
        } finally {
            $this->destroyImage($target);
        }

        if (! $written || $contents === '') {
            throw new RuntimeException('The uploaded image variant could not be generated.');
        }

        $this->put($disk, $path, $contents);
    }

    private function canCopyOriginalJpeg(?string $mime, ?int $width, ?int $height, int $maxSize): bool
    {
        return $mime === 'image/jpeg'
            && (int) $width > 0
            && (int) $height > 0
            && $width <= $maxSize
            && $height <= $maxSize;
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

    private function hasMemoryForImageDecode(?int $width, ?int $height): bool
    {
        if (! $width || ! $height) {
            return true;
        }

        return $this->hasAvailableMemory(($width * $height * 5) + (8 * 1024 * 1024));
    }

    private function hasMemoryForResize(int $width, int $height): bool
    {
        return $this->hasAvailableMemory(($width * $height * 5) + (8 * 1024 * 1024));
    }

    private function hasAvailableMemory(int $estimatedBytes): bool
    {
        $limit = $this->memoryLimitBytes();

        if ($limit <= 0) {
            return true;
        }

        return ($limit - memory_get_usage(true)) > $estimatedBytes;
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
