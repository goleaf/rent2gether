<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomRulesStep extends Component
{
    use HandlesRoomStep;

    public string $roomRulesTextEn = '';

    public string $roomRulesTextRu = '';

    public string $quietHoursTextEn = '';

    public string $quietHoursTextRu = '';

    public string $foodRulesTextEn = '';

    public string $foodRulesTextRu = '';

    public string $conflictInstructionsEn = '';

    public string $conflictInstructionsRu = '';

    public string $specialNotesEn = '';

    public string $specialNotesRu = '';

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('translations');
        $en = $room->translations->firstWhere('locale', 'en');
        $ru = $room->translations->firstWhere('locale', 'ru');

        $this->roomRulesTextEn = (string) ($en?->room_rules_text ?? $room->room_rules_text ?? '');
        $this->roomRulesTextRu = (string) ($ru?->room_rules_text ?? '');
        $this->quietHoursTextEn = (string) ($en?->quiet_hours_text ?? '');
        $this->quietHoursTextRu = (string) ($ru?->quiet_hours_text ?? '');
        $this->foodRulesTextEn = (string) ($en?->food_rules_text ?? '');
        $this->foodRulesTextRu = (string) ($ru?->food_rules_text ?? '');
        $this->conflictInstructionsEn = (string) ($en?->conflict_instructions ?? '');
        $this->conflictInstructionsRu = (string) ($ru?->conflict_instructions ?? '');
        $this->specialNotesEn = (string) ($en?->special_notes ?? '');
        $this->specialNotesRu = (string) ($ru?->special_notes ?? '');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'roomRulesTextEn' => ['nullable', 'string', 'max:5000'],
            'roomRulesTextRu' => ['nullable', 'string', 'max:5000'],
            'quietHoursTextEn' => ['nullable', 'string', 'max:2000'],
            'quietHoursTextRu' => ['nullable', 'string', 'max:2000'],
            'foodRulesTextEn' => ['nullable', 'string', 'max:2000'],
            'foodRulesTextRu' => ['nullable', 'string', 'max:2000'],
            'conflictInstructionsEn' => ['nullable', 'string', 'max:2000'],
            'conflictInstructionsRu' => ['nullable', 'string', 'max:2000'],
            'specialNotesEn' => ['nullable', 'string', 'max:2000'],
            'specialNotesRu' => ['nullable', 'string', 'max:2000'],
        ], attributes: __('room.validation_attributes'));

        $room = $this->room();
        $room->update(['room_rules_text' => $validated['roomRulesTextEn'] ?: null]);

        foreach (['en', 'ru'] as $locale) {
            $suffix = $locale === 'en' ? 'En' : 'Ru';
            $room->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'room_rules_text' => $validated['roomRulesText'.$suffix] ?: null,
                    'quiet_hours_text' => $validated['quietHoursText'.$suffix] ?: null,
                    'food_rules_text' => $validated['foodRulesText'.$suffix] ?: null,
                    'conflict_instructions' => $validated['conflictInstructions'.$suffix] ?: null,
                    'special_notes' => $validated['specialNotes'.$suffix] ?: null,
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
