<?php

namespace App\Actions\Account;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreAvatarVariants
{
    /**
     * @return array{original: string, thumb: string, medium: string}
     */
    public function handle(User $user, UploadedFile $avatar): array
    {
        $extension = $this->extension($avatar);
        $baseName = (string) Str::uuid();
        $directory = 'avatars/'.$user->id;
        $original = $avatar->storeAs($directory, $baseName.'-original.'.$extension, 'public');

        return [
            'original' => $original,
            'thumb' => $this->variant($avatar, $directory.'/'.$baseName.'-thumb.jpg', 160),
            'medium' => $this->variant($avatar, $directory.'/'.$baseName.'-medium.jpg', 480),
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

    private function variant(UploadedFile $avatar, string $path, int $size): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            Storage::disk('public')->put($path, file_get_contents($avatar->getRealPath()));

            return $path;
        }

        $image = imagecreatefromstring(file_get_contents($avatar->getRealPath()));

        if (! $image) {
            Storage::disk('public')->put($path, file_get_contents($avatar->getRealPath()));

            return $path;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min($size / $width, $size / $height, 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagecopyresampled($target, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($target, null, 82);
        $contents = (string) ob_get_clean();

        imagedestroy($image);
        imagedestroy($target);

        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
