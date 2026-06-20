<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlaceTemplate;
use App\Models\User;

class SleepingPlaceTemplateService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $host, array $data): SleepingPlaceTemplate
    {
        return SleepingPlaceTemplate::query()->create([
            'user_id' => $host->id,
            'name' => $data['name'],
            'place_type' => $data['place_type'] ?? null,
            'template_json' => $data['template_json'] ?? [],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);
    }
}
