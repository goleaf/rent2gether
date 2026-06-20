<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageVariantGenerator
{
    /**
     * @return array{path:string,thumb_path:string,mobile_path:string,full_path:string,width:int|null,height:int|null,mime:string|null,size:int|null}
     */
    public function generate(UploadedFile $file, string $directory, string $baseName, string $disk = 'public'): array
    {
        [$width, $height] = $this->dimensions($file);
        $mime = $file->getMimeType();
        $size = $file->getSize();

        if (! $this->canUseGd($file)) {
            $extension = $file->extension() ?: 'jpg';
            $path = $directory.'/'.$baseName.'-full.'.$extension;
            Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

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

        $this->writeJpegVariant($file, $fullPath, 1600, 84, $disk);
        $this->writeJpegVariant($file, $mobilePath, 720, 80, $disk);
        $this->writeJpegVariant($file, $thumbPath, 320, 76, $disk);

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

    private function canUseGd(UploadedFile $file): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagejpeg')
            && is_readable($file->getRealPath());
    }

    private function writeJpegVariant(UploadedFile $file, string $path, int $maxSize, int $quality, string $disk): void
    {
        if ($this->canCopyOriginalJpeg($file, $maxSize)) {
            Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

            return;
        }

        $source = imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (! $source) {
            Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

            return;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min($maxSize / max(1, $width), $maxSize / max(1, $height), 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($target, null, $quality);
        $contents = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        Storage::disk($disk)->put($path, $contents);
    }

    private function canCopyOriginalJpeg(UploadedFile $file, int $maxSize): bool
    {
        $size = @getimagesize($file->getRealPath());

        if (! is_array($size)) {
            return false;
        }

        $width = (int) ($size[0] ?? 0);
        $height = (int) ($size[1] ?? 0);
        $mime = (string) ($size['mime'] ?? $file->getMimeType());

        return $mime === 'image/jpeg'
            && $width > 0
            && $height > 0
            && $width <= $maxSize
            && $height <= $maxSize;
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function dimensions(UploadedFile $file): array
    {
        $size = @getimagesize($file->getRealPath());

        if (! is_array($size)) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }
}
