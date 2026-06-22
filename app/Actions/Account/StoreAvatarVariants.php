<?php

namespace App\Actions\Account;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreAvatarVariants
{
    /**
     * @return array{original: string, thumb: string, medium: string}
     */
    public function handle(User $user, UploadedFile $avatar): array
    {
        $contents = $this->readUpload($avatar);
        $baseName = (string) Str::uuid();
        $directory = 'avatars/'.$user->id;
        $source = $this->sourceImage($contents);

        if (! $source) {
            $extension = $this->extension($avatar);

            return [
                'original' => $this->copyVariant($contents, $directory.'/'.$baseName.'-original.'.$extension),
                'thumb' => $this->copyVariant($contents, $directory.'/'.$baseName.'-thumb.'.$extension),
                'medium' => $this->copyVariant($contents, $directory.'/'.$baseName.'-medium.'.$extension),
            ];
        }

        try {
            return [
                'original' => $this->variant($source, $directory.'/'.$baseName.'-original.webp', 1600, 86),
                'thumb' => $this->variant($source, $directory.'/'.$baseName.'-thumb.webp', 160, 80),
                'medium' => $this->variant($source, $directory.'/'.$baseName.'-medium.webp', 480, 84),
            ];
        } finally {
            $this->destroyImage($source);
        }
    }

    private function extension(UploadedFile $avatar): string
    {
        return match ($avatar->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => $avatar->extension() ?: 'jpg',
        };
    }

    private function readUpload(UploadedFile $avatar): string
    {
        $path = $avatar->getRealPath();

        if (! is_string($path) || ! is_readable($path)) {
            throw new RuntimeException('The uploaded avatar could not be read.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('The uploaded avatar could not be read.');
        }

        return $contents;
    }

    private function sourceImage(string $contents): mixed
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return false;
        }

        return @imagecreatefromstring($contents);
    }

    private function copyVariant(string $contents, string $path): string
    {
        if (Storage::disk('public')->put($path, $contents) === false) {
            throw new RuntimeException('The uploaded avatar variant could not be stored.');
        }

        return $path;
    }

    private function variant(mixed $image, string $path, int $size, int $quality): string
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width < 1 || $height < 1) {
            throw new RuntimeException('The uploaded avatar has invalid dimensions.');
        }

        $scale = min($size / $width, $size / $height, 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        try {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefill($target, 0, 0, $transparent);
            imagecopyresampled($target, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            ob_start();
            $written = imagewebp($target, null, $quality);
            $contents = (string) ob_get_clean();
        } finally {
            $this->destroyImage($target);
        }

        if (! $written || $contents === '') {
            throw new RuntimeException('The uploaded avatar variant could not be generated.');
        }

        if (Storage::disk('public')->put($path, $contents) === false) {
            throw new RuntimeException('The uploaded avatar variant could not be stored.');
        }

        return $path;
    }

    private function destroyImage(mixed $image): void
    {
        if (is_resource($image) || $image instanceof \GdImage) {
            imagedestroy($image);
        }
    }
}
