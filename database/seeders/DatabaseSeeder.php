<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GeoSeeder::class,
            GeoNamesFullSeeder::class,
            AmenityRuleSeeder::class,
            MarketplaceDemoSeeder::class,
        ]);

        if (! (bool) config('geo.geonames.seed_enabled')) {
            $this->call(BulkMarketplaceSeeder::class);
        }
    }
}
