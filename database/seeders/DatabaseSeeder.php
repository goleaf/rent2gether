<?php

namespace Database\Seeders;

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
    }
}
