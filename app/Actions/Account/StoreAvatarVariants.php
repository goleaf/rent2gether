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
        $extension = $this->extension($avatar);
        $baseName = (string) Str::uuid();
        $directory = 'avatars/'.$user->id;
        $original = $avatar->storeAs($directory, $baseName.'-original.'.$extension, 'public');
        $source = $this->sourceImage($contents);

        if ($original === false) {
            throw new RuntimeException('The uploaded avatar could not be stored.');
        }

        $paths = [
            'original' => $original,
        ];

        if (! $source) {
            return [
                ...$paths,
                'thumb' => $this->copyVariant($contents, $directory.'/'.$baseName.'-thumb.'.$extension),
                'medium' => $this->copyVariant($contents, $directory.'/'.$baseName.'-medium.'.$extension),
            ];
        }

        return [
            ...$paths,
            'thumb' => $this->variant($source, $directory.'/'.$baseName.'-thumb.jpg', 160),
            'medium' => $this->variant($source, $directory.'/'.$baseName.'-medium.jpg', 480),
        ];
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
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
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

    private function variant(mixed $image, string $path, int $size): string
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

        imagecopyresampled($target, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        $written = imagejpeg($target, null, 82);
        $contents = (string) ob_get_clean();

        if (! $written || $contents === '') {
            throw new RuntimeException('The uploaded avatar variant could not be generated.');
        }

        if (Storage::disk('public')->put($path, $contents) === false) {
            throw new RuntimeException('The uploaded avatar variant could not be stored.');
        }

        return $path;
    }
}
