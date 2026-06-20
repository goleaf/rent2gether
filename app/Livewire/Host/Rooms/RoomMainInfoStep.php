<?php

namespace App\Livewire\Host\Rooms;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Livewire\Concerns\ManagesLocalizedFormTranslations;
use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoomMainInfoStep extends Component
{
    use HandlesRoomStep;
    use ManagesLocalizedFormTranslations;

    private const TRANSLATION_FIELDS = [
        'title',
        'short_description',
        'full_description',
    ];

    public string $roomNumber = '';

    public string $internalName = '';

    public string $roomType = '';

    public string $livingFormat = '';

    public string $genderPolicy = '';

    public bool $isPrivate = false;

    public bool $isShared = true;

    public bool $isPassThrough = false;

    public bool $isForOnePerson = false;

    public bool $isForCouples = false;

    public bool $isForGroups = false;

    public bool $isForLongStay = true;

    public bool $isForShortStay = true;

    public ?int $sleepingPlacesCount = null;

    public ?int $maxGuests = null;

    public string $status = '';

    public bool $canBookEntireRoom = false;

    public bool $canBookIndividualPlaces = true;

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('translations');
        $this->fillBlankTranslations(self::TRANSLATION_FIELDS);
        $this->loadLocalizedTranslations($room->translations, self::TRANSLATION_FIELDS);
        $this->roomNumber = (string) ($room->room_number ?? '');
        $this->internalName = (string) ($room->internal_name ?? '');
        $this->roomType = (string) ($room->room_type?->value ?? $room->type?->value ?? RoomType::Shared->value);
        $this->livingFormat = (string) ($room->living_format ?? '');
        $this->genderPolicy = (string) ($room->gender_policy?->value ?? $room->gender_type?->value ?? GenderType::Mixed->value);
        $this->isPrivate = (bool) $room->is_private;
        $this->isShared = (bool) ($room->is_shared ?? ! $room->is_private);
        $this->isPassThrough = (bool) $room->is_pass_through;
        $this->isForOnePerson = (bool) $room->is_for_one_person;
        $this->isForCouples = (bool) $room->is_for_couples;
        $this->isForGroups = (bool) $room->is_for_groups;
        $this->isForLongStay = (bool) $room->is_for_long_stay;
        $this->isForShortStay = (bool) $room->is_for_short_stay;
        $this->sleepingPlacesCount = $room->sleeping_places_count ?: $room->beds_count;
        $this->maxGuests = $room->max_guests;
        $this->status = (string) ($room->status?->value ?? RoomStatus::Draft->value);
        $this->canBookEntireRoom = (bool) $room->can_book_entire_room;
        $this->canBookIndividualPlaces = (bool) $room->can_book_individual_places;
    }

    public function save(): void
    {
        $validated = $this->validate([
            ...$this->localizedTranslationRules([
                'title' => ['required', 'string', 'max:160'],
                'short_description' => ['nullable', 'string', 'max:1000'],
                'full_description' => ['nullable', 'string', 'max:5000'],
            ]),
            'roomNumber' => ['nullable', 'string', 'max:80'],
            'internalName' => ['nullable', 'string', 'max:160'],
            'roomType' => ['required', Rule::in(array_column(RoomType::cases(), 'value'))],
            'livingFormat' => ['nullable', 'string', 'max:80'],
            'genderPolicy' => ['required', Rule::in(array_column(GenderType::cases(), 'value'))],
            'isPrivate' => ['boolean'],
            'isShared' => ['boolean'],
            'isPassThrough' => ['boolean'],
            'isForOnePerson' => ['boolean'],
            'isForCouples' => ['boolean'],
            'isForGroups' => ['boolean'],
            'isForLongStay' => ['boolean'],
            'isForShortStay' => ['boolean'],
            'sleepingPlacesCount' => ['nullable', 'integer', 'min:0', 'max:200'],
            'maxGuests' => ['nullable', 'integer', 'min:1', 'max:200'],
            'status' => ['required', Rule::in(array_column(RoomStatus::cases(), 'value'))],
            'canBookEntireRoom' => ['boolean'],
            'canBookIndividualPlaces' => ['boolean'],
        ], attributes: array_merge(
            (array) __('room.validation_attributes'),
            $this->localizedValidationAttributes('room.translation_fields', self::TRANSLATION_FIELDS),
        ));

        $room = $this->room();
        $room->update([
            'title' => $this->firstTranslationValue('title'),
            'room_number' => $validated['roomNumber'] ?: null,
            'internal_name' => $validated['internalName'] ?: null,
            'type' => $validated['roomType'],
            'room_type' => $validated['roomType'],
            'living_format' => $validated['livingFormat'] ?: null,
            'gender_type' => $validated['genderPolicy'],
            'gender_policy' => $validated['genderPolicy'],
            'is_private' => $validated['isPrivate'],
            'is_shared' => $validated['isShared'],
            'is_pass_through' => $validated['isPassThrough'],
            'is_for_one_person' => $validated['isForOnePerson'],
            'is_for_couples' => $validated['isForCouples'],
            'is_for_groups' => $validated['isForGroups'],
            'is_for_long_stay' => $validated['isForLongStay'],
            'is_for_short_stay' => $validated['isForShortStay'],
            'beds_count' => $validated['sleepingPlacesCount'] ?? 0,
            'sleeping_places_count' => $validated['sleepingPlacesCount'] ?? 0,
            'active_sleeping_places_count' => $validated['sleepingPlacesCount'] ?? 0,
            'max_guests' => $validated['maxGuests'],
            'status' => $validated['status'],
            'can_book_entire_room' => $validated['canBookEntireRoom'],
            'can_book_individual_places' => $validated['canBookIndividualPlaces'],
        ]);

        foreach ($this->contentLocales() as $localeData) {
            $locale = $localeData['code'];
            $translation = $validated['translations'][$locale] ?? [];
            $room->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => (string) ($translation['title'] ?? ''),
                    'short_description' => ($translation['short_description'] ?? '') ?: null,
                    'full_description' => ($translation['full_description'] ?? '') ?: null,
                    'summary' => ($translation['short_description'] ?? '') ?: null,
                    'description' => ($translation['full_description'] ?? '') ?: null,
                ],
            );
        }

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-main-info-step');
    }
}
