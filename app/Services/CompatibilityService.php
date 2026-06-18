<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\User;

class CompatibilityService
{
    /**
     * @return array{score: string, warnings: string[], matches: string[]}
     */
    public function check(User $guest, Bed $bed): array
    {
        $warnings = [];
        $matches = [];
        $room = $bed->room;
        $property = $room->property;
        $rules = $property->rules ?? [];

        if ($guest->is_smoker && in_array('no_smoking', $rules)) {
            $warnings[] = 'You indicated you smoke, but smoking is not allowed here.';
        }

        if ($guest->has_pets && in_array('no_pets', $rules)) {
            $warnings[] = 'You have pets, but pets are not allowed here.';
        }

        if ($guest->prefers_quiet && ! in_array('quiet_hours', $rules)) {
            $warnings[] = 'You prefer quiet — this place has no specific quiet hours.';
        }

        if ($guest->preferred_room_gender && $room->gender_type->value !== 'mixed' && $room->gender_type->value !== $guest->preferred_room_gender) {
            $warnings[] = 'This room\'s gender type does not match your preference.';
        }

        if ($bed->type->isBunk() && $bed->type->value === 'bunk_top') {
            $warnings[] = 'This is an upper bunk bed.';
        }

        // Matches
        if ($bed->has_locker) {
            $matches[] = 'Personal locker available.';
        }
        if ($bed->has_outlet) {
            $matches[] = 'Power outlet near the bed.';
        }
        if (in_array('wifi', $property->amenities ?? [])) {
            $matches[] = 'Wi-Fi available.';
        }
        if (in_array('kitchen', $property->amenities ?? [])) {
            $matches[] = 'Kitchen available.';
        }
        if ($room->has_desk) {
            $matches[] = 'Desk for working.';
        }
        if ($room->has_lock) {
            $matches[] = 'Room has a lock.';
        }
        if ($guest->prefers_quiet && in_array('quiet_hours', $rules)) {
            $matches[] = 'Quiet hours enforced.';
        }

        $score = match (true) {
            count($warnings) === 0 && count($matches) >= 3 => 'great',
            count($warnings) === 0 => 'good',
            count($warnings) <= 2 => 'okay',
            default => 'poor',
        };

        return compact('score', 'warnings', 'matches');
    }
}
