<?php

namespace App\Actions\SleepingPlaces;

use App\Enums\SleepingPlaceStatus;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DuplicateSleepingPlaceAction
{
    public function handle(SleepingPlace $sleepingPlace, User $host): SleepingPlace
    {
        return DB::transaction(function () use ($sleepingPlace, $host): SleepingPlace {
            $sleepingPlace->loadMissing(['property', 'translations', 'rules']);

            abort_unless($sleepingPlace->property?->isOwnedBy($host), 403);

            $copy = $sleepingPlace->replicate();
            $copy->display_name = trim(($sleepingPlace->display_name ?: $sleepingPlace->place_number ?: __('host.sleeping_places.default_name')).' '.__('host.sleeping_place_wizard.copy_suffix'));
            $copy->place_number = null;
            $copy->status = SleepingPlaceStatus::Draft->value;
            $copy->save();

            foreach ($sleepingPlace->translations as $translation) {
                $copy->translations()->create([
                    'locale' => $translation->locale,
                    'title' => trim($translation->title.' '.__('host.sleeping_place_wizard.copy_suffix')),
                    'summary' => $translation->summary,
                    'description' => $translation->description,
                    'special_conditions' => $translation->special_conditions,
                    'privacy_notes' => $translation->privacy_notes,
                    'accessibility_notes' => $translation->accessibility_notes,
                ]);
            }

            $copy->rules()->sync($sleepingPlace->getRelation('rules')->pluck('id')->all());

            return $copy->refresh();
        });
    }
}
