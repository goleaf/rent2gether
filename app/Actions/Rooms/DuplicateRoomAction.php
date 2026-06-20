<?php

namespace App\Actions\Rooms;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DuplicateRoomAction
{
    public function handle(Room $room, User $host): Room
    {
        return DB::transaction(function () use ($room, $host): Room {
            $room->loadMissing(['property', 'translations', 'rules']);

            abort_unless($room->property?->isOwnedBy($host), 403);

            $copy = $room->replicate();
            $copy->title = trim($room->title.' '.__('host.room_wizard.copy_suffix'));
            $copy->status = RoomStatus::Draft->value;
            $copy->occupied_places_count = 0;
            $copy->available_places_count = 0;
            $copy->save();

            foreach ($room->translations as $translation) {
                $copy->translations()->create([
                    'locale' => $translation->locale,
                    'title' => trim($translation->title.' '.__('host.room_wizard.copy_suffix')),
                    'summary' => $translation->summary,
                    'description' => $translation->description,
                    'notes' => $translation->notes,
                    'sleeping_arrangement' => $translation->sleeping_arrangement,
                    'privacy_notes' => $translation->privacy_notes,
                ]);
            }

            $copy->rules()->sync($room->getRelation('rules')->pluck('id')->all());

            return $copy->refresh();
        });
    }
}
