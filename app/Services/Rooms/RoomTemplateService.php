<?php

namespace App\Services\Rooms;

use App\Models\RoomTemplate;
use App\Models\User;

class RoomTemplateService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $host, array $data): RoomTemplate
    {
        return RoomTemplate::query()->create([
            'user_id' => $host->id,
            'name' => $data['name'],
            'room_type' => $data['room_type'] ?? null,
            'template_json' => $data['template_json'] ?? [],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);
    }
}
