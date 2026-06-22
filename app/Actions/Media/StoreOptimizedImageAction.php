<?php

namespace App\Actions\Media;

use App\Services\Media\ImageVariantGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreOptimizedImageAction
{
    public function __construct(private readonly ImageVariantGenerator $variants) {}

    /**
     * @return array{path:string,thumb_path:string,mobile_path:string,full_path:string,width:int|null,height:int|null,mime:string|null,size:int|null}
     */
    public function handle(UploadedFile $file, string $directory): array
    {
        return $this->variants->generate($file, $directory, (string) Str::uuid());
    }
}
