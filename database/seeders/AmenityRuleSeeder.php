<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Rule;
use App\Services\Catalog\AmenityRuleCatalog;
use App\Services\Catalog\AmenityRuleLookupService;
use Illuminate\Database\Seeder;

class AmenityRuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AmenityRuleCatalog::amenities() as $item) {
            $amenity = Amenity::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name_normalized' => AmenityRuleCatalog::normalize($item['en']),
                    'category' => $item['category'],
                    'status' => 'active',
                ]
            );

            $amenity->translations()->updateOrCreate(['locale' => 'en'], [
                'name' => $item['en'],
                'name_normalized' => AmenityRuleCatalog::normalize($item['en']),
            ]);
            $amenity->translations()->updateOrCreate(['locale' => 'ru'], [
                'name' => $item['ru'],
                'name_normalized' => AmenityRuleCatalog::normalize($item['ru']),
            ]);
        }

        foreach (AmenityRuleCatalog::rules() as $item) {
            $rule = Rule::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name_normalized' => AmenityRuleCatalog::normalize($item['en']),
                    'category' => $item['category'],
                    'requires_confirmation' => $item['requires_confirmation'],
                    'status' => 'active',
                ]
            );

            $rule->translations()->updateOrCreate(['locale' => 'en'], [
                'name' => $item['en'],
                'name_normalized' => AmenityRuleCatalog::normalize($item['en']),
            ]);
            $rule->translations()->updateOrCreate(['locale' => 'ru'], [
                'name' => $item['ru'],
                'name_normalized' => AmenityRuleCatalog::normalize($item['ru']),
            ]);
        }

        AmenityRuleLookupService::clearAll();
    }
}
