<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SleepingPlaceMainInfoStep extends Component
{
    use HandlesSleepingPlaceStep;

    public string $titleEn = '';

    public string $titleRu = '';

    public string $shortDescriptionEn = '';

    public string $shortDescriptionRu = '';

    public string $placeNumber = '';

    public string $internalName = '';

    public string $sleepingPlaceType = '';

    public string $sleepingPlaceSubtype = '';

    public string $bunkLevel = '';

    public bool $isTopBunk = false;

    public bool $isBottomBunk = false;

    public bool $isSingle = true;

    public bool $isDouble = false;

    public bool $isForOnePerson = true;

    public bool $isForCouple = false;

    public ?int $maxGuests = 1;

    public ?int $minGuestAge = null;

    public ?int $maxGuestAge = null;

    public string $status = '';

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
        $sleepingPlace->loadMissing('translations');

        $en = $sleepingPlace->translations->firstWhere('locale', 'en');
        $ru = $sleepingPlace->translations->firstWhere('locale', 'ru');

        $this->titleEn = (string) ($en?->title ?? $sleepingPlace->display_name ?? '');
        $this->titleRu = (string) ($ru?->title ?? '');
        $this->shortDescriptionEn = (string) ($en?->short_description ?? $en?->summary ?? '');
        $this->shortDescriptionRu = (string) ($ru?->short_description ?? $ru?->summary ?? '');
        $this->placeNumber = (string) ($sleepingPlace->place_number ?? '');
        $this->internalName = (string) ($sleepingPlace->internal_name ?? '');
        $this->sleepingPlaceType = (string) ($sleepingPlace->sleeping_place_type?->value ?? $sleepingPlace->type?->value ?? SleepingPlaceType::Single->value);
        $this->sleepingPlaceSubtype = (string) ($sleepingPlace->sleeping_place_subtype ?? '');
        $this->bunkLevel = (string) ($sleepingPlace->bunk_level ?? '');
        $this->isTopBunk = (bool) $sleepingPlace->is_top_bunk;
        $this->isBottomBunk = (bool) $sleepingPlace->is_bottom_bunk;
        $this->isSingle = (bool) ($sleepingPlace->is_single ?? true);
        $this->isDouble = (bool) $sleepingPlace->is_double;
        $this->isForOnePerson = (bool) ($sleepingPlace->is_for_one_person ?? true);
        $this->isForCouple = (bool) $sleepingPlace->is_for_couple;
        $this->maxGuests = $sleepingPlace->max_guests;
        $this->minGuestAge = $sleepingPlace->min_guest_age;
        $this->maxGuestAge = $sleepingPlace->max_guest_age;
        $this->status = (string) ($sleepingPlace->status?->value ?? SleepingPlaceStatus::Draft->value);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'titleEn' => ['required', 'string', 'max:160'],
            'titleRu' => ['required', 'string', 'max:160'],
            'shortDescriptionEn' => ['nullable', 'string', 'max:1000'],
            'shortDescriptionRu' => ['nullable', 'string', 'max:1000'],
            'placeNumber' => ['nullable', 'string', 'max:80'],
            'internalName' => ['nullable', 'string', 'max:160'],
            'sleepingPlaceType' => ['required', Rule::in(array_column(SleepingPlaceType::cases(), 'value'))],
            'sleepingPlaceSubtype' => ['nullable', 'string', 'max:80'],
            'bunkLevel' => ['nullable', 'string', 'max:40'],
            'isTopBunk' => ['boolean'],
            'isBottomBunk' => ['boolean'],
            'isSingle' => ['boolean'],
            'isDouble' => ['boolean'],
            'isForOnePerson' => ['boolean'],
            'isForCouple' => ['boolean'],
            'maxGuests' => ['nullable', 'integer', 'min:1', 'max:4'],
            'minGuestAge' => ['nullable', 'integer', 'min:0', 'max:120'],
            'maxGuestAge' => ['nullable', 'integer', 'min:0', 'max:120'],
            'status' => ['required', Rule::in(array_column(SleepingPlaceStatus::cases(), 'value'))],
        ], attributes: __('sleeping_place.validation_attributes'));

        $place = $this->sleepingPlace();
        $place->update([
            'display_name' => $validated['titleEn'],
            'type' => $validated['sleepingPlaceType'],
            'sleeping_place_type' => $validated['sleepingPlaceType'],
            'sleeping_place_subtype' => $validated['sleepingPlaceSubtype'] ?: null,
            'place_number' => $validated['placeNumber'] ?: null,
            'internal_name' => $validated['internalName'] ?: null,
            'bunk_level' => $validated['bunkLevel'] ?: null,
            'is_top_bunk' => $validated['isTopBunk'],
            'is_bottom_bunk' => $validated['isBottomBunk'],
            'is_single' => $validated['isSingle'],
            'is_double' => $validated['isDouble'],
            'is_for_one_person' => $validated['isForOnePerson'],
            'is_for_couple' => $validated['isForCouple'],
            'max_guests' => $validated['maxGuests'] ?? 1,
            'min_guest_age' => $validated['minGuestAge'],
            'max_guest_age' => $validated['maxGuestAge'],
            'status' => $validated['status'],
        ]);

        foreach (['en', 'ru'] as $locale) {
            $suffix = $locale === 'en' ? 'En' : 'Ru';
            $place->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $validated['title'.$suffix],
                    'short_description' => $validated['shortDescription'.$suffix] ?: null,
                    'summary' => $validated['shortDescription'.$suffix] ?: null,
                ],
            );
        }

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-main-info-step');
    }
}
