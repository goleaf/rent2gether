<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Concerns\ManagesLocalizedFormTranslations;
use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SleepingPlaceMainInfoStep extends Component
{
    use HandlesSleepingPlaceStep;
    use ManagesLocalizedFormTranslations;

    private const TRANSLATION_FIELDS = [
        'title',
        'short_description',
    ];

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
        $this->fillBlankTranslations(self::TRANSLATION_FIELDS);
        $this->loadLocalizedTranslations($sleepingPlace->translations, self::TRANSLATION_FIELDS);
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
            ...$this->localizedTranslationRules([
                'title' => ['required', 'string', 'max:160'],
                'short_description' => ['nullable', 'string', 'max:1000'],
            ]),
            'placeNumber' => ['nullable', 'string', 'max:80'],
            'internalName' => ['nullable', 'string', 'max:160'],
            'sleepingPlaceType' => ['required', Rule::in(array_column(SleepingPlaceType::cases(), 'value'))],
            'sleepingPlaceSubtype' => ['nullable', 'string', 'max:80'],
            'bunkLevel' => ['nullable', Rule::in(['', 'top', 'middle', 'bottom'])],
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
        ], attributes: array_merge(
            (array) __('sleeping_place.validation_attributes'),
            $this->localizedValidationAttributes('sleeping_place.translation_fields', self::TRANSLATION_FIELDS),
        ));

        if (! $this->validateCapacityAndPlacement($validated)) {
            return;
        }

        $isTopBunk = (bool) (
            $validated['isTopBunk']
            || $validated['sleepingPlaceType'] === SleepingPlaceType::BunkTop->value
            || $validated['bunkLevel'] === 'top'
        );
        $isBottomBunk = (bool) (
            $validated['isBottomBunk']
            || $validated['sleepingPlaceType'] === SleepingPlaceType::BunkBottom->value
            || $validated['bunkLevel'] === 'bottom'
        );
        $isDouble = (bool) (
            $validated['isDouble']
            || in_array($validated['sleepingPlaceType'], [SleepingPlaceType::Double->value, SleepingPlaceType::SofaBed->value], true)
        );
        $isSingle = (bool) (
            $validated['isSingle']
            || in_array($validated['sleepingPlaceType'], [
                SleepingPlaceType::Single->value,
                SleepingPlaceType::BunkTop->value,
                SleepingPlaceType::BunkBottom->value,
                SleepingPlaceType::Mattress->value,
                SleepingPlaceType::FoldOut->value,
                SleepingPlaceType::Capsule->value,
            ], true)
        );
        $isDoublePlace = (bool) ($isDouble || $validated['isForCouple']);
        $maxGuests = $validated['maxGuests'] ?? ($isDoublePlace ? 2 : 1);
        $foundationPlaceType = $this->foundationPlaceType($validated['sleepingPlaceType']);

        $place = $this->sleepingPlace();
        $place->update([
            'title' => $this->firstTranslationValue('title'),
            'display_name' => $this->firstTranslationValue('title'),
            'place_type' => $foundationPlaceType,
            'bed_type' => $validated['sleepingPlaceSubtype'] ?: $foundationPlaceType,
            'type' => $validated['sleepingPlaceType'],
            'sleeping_place_type' => $validated['sleepingPlaceType'],
            'sleeping_place_subtype' => $validated['sleepingPlaceSubtype'] ?: null,
            'place_number' => $validated['placeNumber'] ?: null,
            'internal_name' => $validated['internalName'] ?: null,
            'bunk_level' => $validated['bunkLevel'] ?: null,
            'is_top_bunk' => $isTopBunk,
            'is_bottom_bunk' => $isBottomBunk,
            'is_single' => $isSingle,
            'is_double' => $isDouble,
            'is_double_place' => $isDoublePlace,
            'is_for_one_person' => $validated['isForOnePerson'],
            'is_for_couple' => $validated['isForCouple'],
            'max_guests' => $maxGuests,
            'max_guests_count' => $maxGuests,
            'min_guest_age' => $validated['minGuestAge'],
            'max_guest_age' => $validated['maxGuestAge'],
            'status' => $validated['status'],
        ]);

        foreach ($this->contentLocales() as $localeData) {
            $locale = $localeData['code'];
            $translation = $validated['translations'][$locale] ?? [];
            $place->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => (string) ($translation['title'] ?? ''),
                    'short_description' => ($translation['short_description'] ?? '') ?: null,
                    'summary' => ($translation['short_description'] ?? '') ?: null,
                ],
            );
        }

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-main-info-step');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateCapacityAndPlacement(array $validated): bool
    {
        $topSelected = $validated['isTopBunk']
            || $validated['sleepingPlaceType'] === SleepingPlaceType::BunkTop->value
            || $validated['bunkLevel'] === 'top';
        $bottomSelected = $validated['isBottomBunk']
            || $validated['sleepingPlaceType'] === SleepingPlaceType::BunkBottom->value
            || $validated['bunkLevel'] === 'bottom';

        if ($topSelected && $bottomSelected) {
            $this->addError('isTopBunk', __('sleeping_place.validation.only_one_bunk_level'));

            return false;
        }

        if ($validated['isForOnePerson'] && $validated['isForCouple']) {
            $this->addError('isForCouple', __('sleeping_place.validation.one_person_or_couple'));

            return false;
        }

        if ($validated['isForCouple'] && ($validated['maxGuests'] ?? 1) < 2) {
            $this->addError('maxGuests', __('sleeping_place.validation.couple_requires_two_guests'));

            return false;
        }

        if (
            $validated['minGuestAge'] !== null
            && $validated['maxGuestAge'] !== null
            && $validated['minGuestAge'] > $validated['maxGuestAge']
        ) {
            $this->addError('maxGuestAge', __('sleeping_place.validation.max_age_after_min_age'));

            return false;
        }

        return true;
    }

    private function foundationPlaceType(string $type): string
    {
        return match ($type) {
            SleepingPlaceType::Single->value => 'single_bed',
            SleepingPlaceType::Double->value => 'double_bed',
            SleepingPlaceType::BunkTop->value => 'top_bunk',
            SleepingPlaceType::BunkBottom->value => 'bottom_bunk',
            SleepingPlaceType::FoldOut->value => 'folding_bed',
            SleepingPlaceType::SofaBed->value => 'sofa',
            default => $type,
        };
    }
}
