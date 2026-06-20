<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Concerns\ManagesLocalizedFormTranslations;
use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomRulesStep extends Component
{
    use HandlesRoomStep;
    use ManagesLocalizedFormTranslations;

    private const TRANSLATION_FIELDS = [
        'room_rules_text',
        'quiet_hours_text',
        'food_rules_text',
        'conflict_instructions',
        'special_notes',
    ];

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('translations');
        $this->fillBlankTranslations(self::TRANSLATION_FIELDS);
        $this->loadLocalizedTranslations($room->translations, self::TRANSLATION_FIELDS);
    }

    public function save(): void
    {
        $validated = $this->validate([
            ...$this->localizedTranslationRules([
                'room_rules_text' => ['nullable', 'string', 'max:5000'],
                'quiet_hours_text' => ['nullable', 'string', 'max:2000'],
                'food_rules_text' => ['nullable', 'string', 'max:2000'],
                'conflict_instructions' => ['nullable', 'string', 'max:2000'],
                'special_notes' => ['nullable', 'string', 'max:2000'],
            ]),
        ], attributes: array_merge(
            (array) __('room.validation_attributes'),
            $this->localizedValidationAttributes('room.rule_translation_fields', self::TRANSLATION_FIELDS),
        ));

        $room = $this->room();
        $room->update(['room_rules_text' => $this->firstTranslationValue('room_rules_text') ?: null]);

        foreach ($this->contentLocales() as $localeData) {
            $locale = $localeData['code'];
            $translation = $validated['translations'][$locale] ?? [];
            $room->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'room_rules_text' => ($translation['room_rules_text'] ?? '') ?: null,
                    'quiet_hours_text' => ($translation['quiet_hours_text'] ?? '') ?: null,
                    'food_rules_text' => ($translation['food_rules_text'] ?? '') ?: null,
                    'conflict_instructions' => ($translation['conflict_instructions'] ?? '') ?: null,
                    'special_notes' => ($translation['special_notes'] ?? '') ?: null,
                ],
            );
        }

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-rules-step');
    }
}
