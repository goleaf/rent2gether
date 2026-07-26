<?php

namespace Database\Seeders;

use App\Models\MediaItem;
use App\Services\Media\DemoMediaFileService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GeoSeeder::class,
            AmenityRuleSeeder::class,
            MarketplaceDemoSeeder::class,
            DemoHostGuestSeeder::class,
            BulkMarketplaceSeeder::class,
        ]);

        $this->ensureDemoMediaFiles();
    }

    private function ensureDemoMediaFiles(): void
    {
        $files = app(DemoMediaFileService::class);

        MediaItem::query()
            ->select([
                'id',
                'disk',
                'path',
                'thumbnail_path',
                'thumb_path',
                'mobile_path',
                'full_path',
                'width',
                'height',
                'alt_text',
            ])
            ->where(function ($query): void {
                $query->where('path', 'like', 'demo-media/%')
                    ->orWhere('path', 'like', 'bulk-demo/%');
            })
            ->chunkById(200, function ($mediaItems) use ($files): void {
                $files->ensureForMediaItems($mediaItems);
            });
    }
}
