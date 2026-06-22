<?php

namespace App\Livewire\Host;

use App\Actions\Media\DeleteMediaItemAction;
use App\Actions\Media\StoreMediaItemAction;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Concerns\BuildsLocalizedMediaCaptions;
use App\Livewire\Concerns\ManagesLocalizedFormTranslations;
use App\Livewire\Host\Concerns\BuildsWizardPhotoPreviews;
use App\Models\MediaItem;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\Calendar\SleepingPlaceCalendarBootstrapService;
use App\Services\Catalog\AmenityRuleLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SleepingPlaceForm extends Component
{
    use BuildsLocalizedMediaCaptions;
    use BuildsWizardPhotoPreviews;
    use ManagesLocalizedFormTranslations;
    use WithFileUploads;

    private const STEP_COUNT = 7;

    private const PHOTO_FIELDS = [
        'exactPhoto' => 'exact_place',
        'detailPhoto' => 'detail',
    ];

    private const TRANSLATION_FIELDS = [
        'title',
        'description',
        'special_conditions',
    ];

    public ?int $roomId = null;

    public ?int $propertyId = null;

    public ?int $sleepingPlaceId = null;

    public int $step = 1;

    public bool $wasSaved = false;

    public string $placeNumber = '';

    public string $displayName = '';

    public string $type = 'single';

    public string $status = 'draft';

    public string $bunkLevel = '';

    public ?int $lengthCm = null;

    public ?int $widthCm = null;

    public string $mattressType = '';

    public string $mattressFirmness = '';

    public bool $hasPillow = true;

    public bool $hasBlanket = true;

    public bool $hasBedding = true;

    public bool $hasTowel = false;

    public bool $hasCurtain = false;

    public bool $hasLamp = false;

    public bool $hasPowerSocket = false;

    public bool $hasUsb = false;

    public bool $hasShelf = false;

    public bool $hasHook = false;

    public bool $hasLocker = false;

    public bool $lockerHasLock = false;

    public bool $hasLuggageSpace = false;

    public bool $nearWindow = false;

    public bool $nearDoor = false;

    public bool $nearRadiator = false;

    public bool $nearAirConditioner = false;

    public string $privacyLevel = '';

    public string $noiseLevel = '';

    public bool $suitableForTallPerson = false;

    public bool $suitableForElderly = false;

    public bool $suitableForLimitedMobility = false;

    public ?int $maxGuests = 1;

    public ?int $minGuestAge = null;

    public ?int $maxGuestAge = null;

    public ?float $basePricePerNight = null;

    public ?float $weeklyPrice = null;

    public ?float $monthlyPrice = null;

    public ?float $weekendPrice = null;

    public ?float $holidayPrice = null;

    public ?float $cleaningFee = 0;

    public ?float $depositAmount = 0;

    public string $currency = 'EUR';

    public ?int $minNights = 1;

    public ?int $maxNights = null;

    public bool $instantBookingEnabled = false;

    public bool $requiresHostApproval = true;

    public bool $extensionsAllowed = true;

    /** @var list<int> */
    public array $ruleIds = [];

    public $exactPhoto = null;

    public $detailPhoto = null;

    public function mount(Room $room, ?SleepingPlace $sleepingPlace = null): void
    {
        $this->fillBlankTranslations(self::TRANSLATION_FIELDS);

        $room->loadMissing('property');

        abort_unless($room->property?->isOwnedBy(auth()->user()), 403);

        if ($sleepingPlace?->exists) {
            abort_unless((int) $sleepingPlace->room_id === (int) $room->id, 404);
        }

        $this->roomId = $room->id;
        $this->propertyId = $room->property_id;

        if (! $sleepingPlace?->exists) {
            return;
        }

        $sleepingPlace->load(['translations', 'rules:id']);

        $this->sleepingPlaceId = $sleepingPlace->id;
        $this->placeNumber = $sleepingPlace->place_number ?: '';
        $this->displayName = $sleepingPlace->display_name ?: '';
        $this->type = $sleepingPlace->type?->value ?? SleepingPlaceType::Single->value;
        $this->status = $sleepingPlace->status?->value ?? SleepingPlaceStatus::Draft->value;
        $this->bunkLevel = $sleepingPlace->bunk_level ?: '';
        $this->lengthCm = $sleepingPlace->length_cm;
        $this->widthCm = $sleepingPlace->width_cm;
        $this->mattressType = $sleepingPlace->mattress_type ?: '';
        $this->mattressFirmness = $sleepingPlace->mattress_firmness ?: '';
        $this->hasPillow = (bool) $sleepingPlace->has_pillow;
        $this->hasBlanket = (bool) $sleepingPlace->has_blanket;
        $this->hasBedding = (bool) $sleepingPlace->has_bedding;
        $this->hasTowel = (bool) $sleepingPlace->has_towel;
        $this->hasCurtain = (bool) $sleepingPlace->has_curtain;
        $this->hasLamp = (bool) $sleepingPlace->has_lamp;
        $this->hasPowerSocket = (bool) $sleepingPlace->has_power_socket;
        $this->hasUsb = (bool) $sleepingPlace->has_usb;
        $this->hasShelf = (bool) $sleepingPlace->has_shelf;
        $this->hasHook = (bool) $sleepingPlace->has_hook;
        $this->hasLocker = (bool) $sleepingPlace->has_locker;
        $this->lockerHasLock = (bool) $sleepingPlace->locker_has_lock;
        $this->hasLuggageSpace = (bool) $sleepingPlace->has_luggage_space;
        $this->nearWindow = (bool) $sleepingPlace->near_window;
        $this->nearDoor = (bool) $sleepingPlace->near_door;
        $this->nearRadiator = (bool) $sleepingPlace->near_radiator;
        $this->nearAirConditioner = (bool) $sleepingPlace->near_air_conditioner;
        $this->privacyLevel = $sleepingPlace->privacy_level ?: '';
        $this->noiseLevel = $sleepingPlace->noise_level ?: '';
        $this->suitableForTallPerson = (bool) $sleepingPlace->suitable_for_tall_person;
        $this->suitableForElderly = (bool) $sleepingPlace->suitable_for_elderly;
        $this->suitableForLimitedMobility = (bool) ($sleepingPlace->suitable_for_limited_mobility || $sleepingPlace->is_accessible);
        $this->maxGuests = $sleepingPlace->max_guests;
        $this->minGuestAge = $sleepingPlace->min_guest_age;
        $this->maxGuestAge = $sleepingPlace->max_guest_age;
        $this->basePricePerNight = $sleepingPlace->base_price_per_night === null ? null : (float) $sleepingPlace->base_price_per_night;
        $this->weeklyPrice = $sleepingPlace->weekly_price === null ? null : (float) $sleepingPlace->weekly_price;
        $this->monthlyPrice = $sleepingPlace->monthly_price === null ? null : (float) $sleepingPlace->monthly_price;
        $this->weekendPrice = $sleepingPlace->weekend_price === null ? null : (float) $sleepingPlace->weekend_price;
        $this->holidayPrice = $sleepingPlace->holiday_price === null ? null : (float) $sleepingPlace->holiday_price;
        $this->cleaningFee = (float) $sleepingPlace->cleaning_fee;
        $this->depositAmount = (float) $sleepingPlace->deposit_amount;
        $this->currency = $sleepingPlace->currency ?: 'EUR';
        $this->minNights = $sleepingPlace->min_nights;
        $this->maxNights = $sleepingPlace->max_nights;
        $this->instantBookingEnabled = (bool) $sleepingPlace->instant_booking_enabled;
        $this->requiresHostApproval = (bool) $sleepingPlace->requires_host_approval;
        $this->extensionsAllowed = (bool) $sleepingPlace->extensions_allowed;
        $this->ruleIds = $sleepingPlace->getRelation('rules')->pluck('id')->map(fn (int $id): int => $id)->all();

        $this->loadLocalizedTranslations($sleepingPlace->translations, self::TRANSLATION_FIELDS);
    }

    public function nextStep(): void
    {
        $this->saveStep();
        $this->step = min(self::STEP_COUNT, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->wasSaved = false;
    }

    public function saveStep(): void
    {
        $this->validate($this->rulesForStep($this->step), attributes: $this->validationAttributes());

        $sleepingPlace = $this->persistSleepingPlace($this->intermediateStatus());
        $this->syncStepRelations($sleepingPlace, $this->step);
        $this->wasSaved = true;
    }

    public function publish()
    {
        $this->validate($this->allRules(), attributes: $this->validationAttributes());

        $sleepingPlace = $this->persistSleepingPlace($this->status ?: SleepingPlaceStatus::Draft->value);
        $this->syncRules($sleepingPlace);
        $this->syncPhotos($sleepingPlace);

        session()->flash('success', $this->sleepingPlaceId
            ? __('notifications.flash.sleeping_place_updated')
            : __('notifications.flash.sleeping_place_created'));

        return $this->redirect(route('host.sleeping-places.index', [
            'locale' => app()->getLocale(),
            'room' => $sleepingPlace->room_id,
        ]), navigate: true);
    }

    /**
     * @return array<string, array{url:string,caption:string}>
     */
    #[Computed]
    public function savedPhotoPreviews(): array
    {
        $sleepingPlace = $this->sleepingPlaceModel();

        if (! $sleepingPlace) {
            return [];
        }

        return $sleepingPlace->mediaItems()
            ->select([
                'id',
                'mediable_type',
                'mediable_id',
                'collection',
                'disk',
                'path',
                'thumbnail_path',
                'thumb_path',
                'mobile_path',
                'full_path',
                'alt_text',
                'sort_order',
                'is_primary',
                'is_cover',
                'status',
            ])
            ->with(['translations:id,media_item_id,locale,caption'])
            ->whereIn('collection', array_values(self::PHOTO_FIELDS))
            ->active()
            ->orderByDesc('is_primary')
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->unique('collection')
            ->mapWithKeys(fn (MediaItem $item): array => [
                (string) $item->collection => [
                    'url' => $item->imageUrl('thumb'),
                    'caption' => $item->localizedCaption() ?: __('media.default_caption'),
                ],
            ])
            ->all();
    }

    /**
     * @return list<array{number:int,title:string}>
     */
    public function wizardSteps(): array
    {
        return collect(range(1, self::STEP_COUNT))
            ->map(fn (int $step): array => [
                'number' => $step,
                'title' => __('host.sleeping_place_wizard.steps.'.$step.'.title'),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function typeOptions(): array
    {
        return collect(SleepingPlaceType::cases())
            ->mapWithKeys(fn (SleepingPlaceType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return collect([
            SleepingPlaceStatus::Draft,
            SleepingPlaceStatus::Active,
            SleepingPlaceStatus::Hidden,
            SleepingPlaceStatus::Unavailable,
            SleepingPlaceStatus::Repair,
        ])->mapWithKeys(fn (SleepingPlaceStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function levelOptions(string $group): array
    {
        return collect(['low', 'moderate', 'good', 'high'])
            ->mapWithKeys(fn (string $value): array => [$value => __('host.sleeping_place_wizard.options.'.$group.'.'.$value)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function firmnessOptions(): array
    {
        return collect(['soft', 'medium', 'firm', 'extra_firm'])
            ->mapWithKeys(fn (string $value): array => [$value => __('host.sleeping_place_wizard.options.mattress_firmness.'.$value)])
            ->all();
    }

    /**
     * @return list<array{key:string,label:string,done:bool}>
     */
    public function readinessChecklist(): array
    {
        $sleepingPlace = $this->sleepingPlaceModel();
        $photoCount = $sleepingPlace?->mediaItems()->count() ?? 0;
        $calendarCount = $sleepingPlace?->availabilityDays()->count() ?? 0;
        $rulesCount = $sleepingPlace?->rules()->count() ?? 0;

        return [
            [
                'key' => 'title',
                'label' => __('host.sleeping_place_wizard.readiness.title_field'),
                'done' => $this->hasEveryLocaleValue('title'),
            ],
            [
                'key' => 'exact_photo',
                'label' => __('host.sleeping_place_wizard.readiness.exact_photo'),
                'done' => $photoCount > 0 || $this->exactPhoto instanceof UploadedFile,
            ],
            [
                'key' => 'price',
                'label' => __('host.sleeping_place_wizard.readiness.price'),
                'done' => (float) $this->basePricePerNight > 0,
            ],
            [
                'key' => 'calendar',
                'label' => __('host.sleeping_place_wizard.readiness.calendar'),
                'done' => $calendarCount > 0,
            ],
            [
                'key' => 'rules',
                'label' => __('host.sleeping_place_wizard.readiness.rules'),
                'done' => $rulesCount > 0 || $this->ruleIds !== [],
            ],
        ];
    }

    #[Computed]
    public function room(): Room
    {
        $room = Room::query()
            ->select(['id', 'property_id', 'title', 'room_number'])
            ->with(['property:id,host_user_id,user_id,title'])
            ->findOrFail($this->roomId);

        abort_unless($room->property?->isOwnedBy(auth()->user()), 403);

        return $room;
    }

    #[Computed]
    public function ruleOptions(): array
    {
        return app(AmenityRuleLookupService::class)->ruleOptions(
            locale: app()->getLocale(),
            categories: ['quiet_hours', 'shared_room_behavior', 'security', 'keys'],
        );
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-place-form')
            ->layout('layouts.app', ['title' => __('host.sleeping_place_wizard.title')]);
    }

    public function hostHintStep(): string
    {
        return match ($this->step) {
            4 => 'pricing',
            5 => 'description',
            6 => 'rules',
            7 => 'photos',
            default => 'overview',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'placeNumber' => ['nullable', 'string', 'max:50'],
                'displayName' => ['required', 'string', 'max:255'],
                'type' => ['required', ValidationRule::enum(SleepingPlaceType::class)],
                'status' => ['required', ValidationRule::in([
                    SleepingPlaceStatus::Draft->value,
                    SleepingPlaceStatus::Active->value,
                    SleepingPlaceStatus::Hidden->value,
                    SleepingPlaceStatus::Unavailable->value,
                    SleepingPlaceStatus::Repair->value,
                ])],
                'bunkLevel' => ['nullable', 'string', 'max:50'],
                'lengthCm' => ['nullable', 'integer', 'min:50', 'max:300'],
                'widthCm' => ['nullable', 'integer', 'min:40', 'max:250'],
            ],
            2 => [
                'mattressType' => ['nullable', 'string', 'max:80'],
                'mattressFirmness' => ['nullable', ValidationRule::in(['soft', 'medium', 'firm', 'extra_firm'])],
                'hasPillow' => ['boolean'],
                'hasBlanket' => ['boolean'],
                'hasBedding' => ['boolean'],
                'hasTowel' => ['boolean'],
                'hasCurtain' => ['boolean'],
                'hasLamp' => ['boolean'],
                'hasPowerSocket' => ['boolean'],
                'hasUsb' => ['boolean'],
                'hasShelf' => ['boolean'],
                'hasHook' => ['boolean'],
                'hasLocker' => ['boolean'],
                'lockerHasLock' => ['boolean'],
                'hasLuggageSpace' => ['boolean'],
            ],
            3 => [
                'nearWindow' => ['boolean'],
                'nearDoor' => ['boolean'],
                'nearRadiator' => ['boolean'],
                'nearAirConditioner' => ['boolean'],
                'privacyLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'noiseLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'suitableForTallPerson' => ['boolean'],
                'suitableForElderly' => ['boolean'],
                'suitableForLimitedMobility' => ['boolean'],
                'maxGuests' => ['required', 'integer', 'min:1', 'max:10'],
                'minGuestAge' => ['nullable', 'integer', 'min:0', 'max:120'],
                'maxGuestAge' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:minGuestAge'],
            ],
            4 => [
                'basePricePerNight' => ['required', 'numeric', 'min:0', 'max:100000'],
                'weeklyPrice' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                'monthlyPrice' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                'weekendPrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'holidayPrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'cleaningFee' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'depositAmount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'currency' => ['required', 'string', 'size:3'],
                'minNights' => ['required', 'integer', 'min:1', 'max:365'],
                'maxNights' => ['nullable', 'integer', 'min:1', 'max:3650', 'gte:minNights'],
                'instantBookingEnabled' => ['boolean'],
                'requiresHostApproval' => ['boolean'],
                'extensionsAllowed' => ['boolean'],
            ],
            5 => $this->localizedTranslationRules([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:4000'],
                'special_conditions' => ['nullable', 'string', 'max:2000'],
            ]),
            6 => [
                'ruleIds' => ['array'],
                'ruleIds.*' => ['integer', 'exists:rules,id'],
            ],
            7 => $this->photoRules(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function allRules(): array
    {
        return collect(range(1, self::STEP_COUNT))
            ->flatMap(fn (int $step): array => $this->rulesForStep($step))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function photoRules(): array
    {
        return collect(array_keys(self::PHOTO_FIELDS))
            ->mapWithKeys(fn (string $field): array => [
                $field => ['nullable', 'image', 'max:2048', 'dimensions:min_width=1,min_height=1'],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('host.sleeping_place_wizard.validation_attributes');

        return array_merge(
            is_array($attributes) ? $attributes : [],
            $this->localizedValidationAttributes('host.sleeping_place_wizard.translation_fields', self::TRANSLATION_FIELDS),
        );
    }

    private function persistSleepingPlace(string $status): SleepingPlace
    {
        $room = $this->room;
        $displayName = $this->displayName ?: $this->firstTranslationValue('title') ?: __('host.sleeping_places.default_name');
        $basePrice = $this->basePricePerNight ?? 0;

        $data = [
            'room_id' => $room->id,
            'property_id' => $room->property_id,
            'type' => $this->type,
            'status' => $status,
            'place_number' => $this->blankToNull($this->placeNumber),
            'display_name' => $displayName,
            'bunk_level' => $this->blankToNull($this->bunkLevel),
            'length_cm' => $this->lengthCm,
            'width_cm' => $this->widthCm,
            'mattress_type' => $this->blankToNull($this->mattressType),
            'mattress_firmness' => $this->blankToNull($this->mattressFirmness),
            'has_pillow' => $this->hasPillow,
            'has_blanket' => $this->hasBlanket,
            'has_bedding' => $this->hasBedding,
            'has_towel' => $this->hasTowel,
            'has_curtain' => $this->hasCurtain,
            'has_lamp' => $this->hasLamp,
            'has_power_socket' => $this->hasPowerSocket,
            'has_usb' => $this->hasUsb,
            'has_shelf' => $this->hasShelf,
            'has_hook' => $this->hasHook,
            'has_locker' => $this->hasLocker,
            'locker_has_lock' => $this->lockerHasLock,
            'has_luggage_space' => $this->hasLuggageSpace,
            'near_window' => $this->nearWindow,
            'near_door' => $this->nearDoor,
            'near_radiator' => $this->nearRadiator,
            'near_air_conditioner' => $this->nearAirConditioner,
            'privacy_level' => $this->blankToNull($this->privacyLevel),
            'noise_level' => $this->blankToNull($this->noiseLevel),
            'is_accessible' => $this->suitableForLimitedMobility,
            'suitable_for_tall_person' => $this->suitableForTallPerson,
            'suitable_for_elderly' => $this->suitableForElderly,
            'suitable_for_limited_mobility' => $this->suitableForLimitedMobility,
            'max_guests' => $this->maxGuests ?: 1,
            'min_guest_age' => $this->minGuestAge,
            'max_guest_age' => $this->maxGuestAge,
            'base_price_per_night' => $basePrice,
            'weekly_price' => $this->weeklyPrice,
            'monthly_price' => $this->monthlyPrice,
            'weekend_price' => $this->weekendPrice,
            'holiday_price' => $this->holidayPrice,
            'cleaning_fee' => $this->cleaningFee ?: 0,
            'deposit_amount' => $this->depositAmount ?: 0,
            'currency' => strtoupper($this->currency),
            'min_nights' => $this->minNights ?: 1,
            'max_nights' => $this->maxNights,
            'instant_booking_enabled' => $this->instantBookingEnabled,
            'requires_host_approval' => $this->requiresHostApproval,
            'extensions_allowed' => $this->extensionsAllowed,
        ];

        $sleepingPlace = $this->sleepingPlaceModel();

        if ($sleepingPlace) {
            $sleepingPlace->update($data);
        } else {
            $sleepingPlace = SleepingPlace::query()->create($data);
            $this->sleepingPlaceId = $sleepingPlace->id;
        }

        app(SleepingPlaceCalendarBootstrapService::class)->bootstrap($sleepingPlace);
        $this->syncTranslations($sleepingPlace);

        return $sleepingPlace->refresh();
    }

    private function syncStepRelations(SleepingPlace $sleepingPlace, int $step): void
    {
        if ($step >= 6) {
            $this->syncRules($sleepingPlace);
        }

        if ($step >= 7) {
            $this->syncPhotos($sleepingPlace);
        }
    }

    private function syncRules(SleepingPlace $sleepingPlace): void
    {
        $sleepingPlace->rules()->sync($this->integerIds($this->ruleIds));
    }

    private function syncTranslations(SleepingPlace $sleepingPlace): void
    {
        foreach ($this->contentLocales() as $localeData) {
            $locale = $localeData['code'];
            $translation = $this->translations[$locale] ?? [];
            $title = $this->blankToNull($translation['title'] ?? '') ?: $this->blankToNull($this->displayName);

            if (! $title) {
                continue;
            }

            $sleepingPlace->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title,
                    'summary' => null,
                    'description' => $this->blankToNull($translation['description']),
                    'special_conditions' => $this->blankToNull($translation['special_conditions']),
                    'privacy_notes' => $this->blankToNull($translation['special_conditions']),
                ],
            );
        }
    }

    private function syncPhotos(SleepingPlace $sleepingPlace): void
    {
        $store = app(StoreMediaItemAction::class);
        $delete = app(DeleteMediaItemAction::class);

        foreach (self::PHOTO_FIELDS as $field => $collection) {
            $photo = $this->{$field};

            if (! $photo instanceof UploadedFile) {
                continue;
            }

            $sleepingPlace->mediaItems()
                ->where('collection', $collection)
                ->get()
                ->each(function ($media) use ($delete): void {
                    $delete->handle($media);
                });

            $store->handle(
                owner: $sleepingPlace,
                file: $photo,
                user: auth()->user(),
                collection: $collection,
                captions: $this->localizedCaptions('host.sleeping_place_wizard.photos.'.$collection),
                makePrimary: $collection === 'exact_place',
            );

            $this->{$field} = null;
        }
    }

    private function intermediateStatus(): string
    {
        if (! $this->sleepingPlaceId) {
            return SleepingPlaceStatus::Draft->value;
        }

        return $this->status ?: SleepingPlaceStatus::Draft->value;
    }

    private function sleepingPlaceModel(): ?SleepingPlace
    {
        if (! $this->sleepingPlaceId) {
            return null;
        }

        $sleepingPlace = SleepingPlace::query()
            ->with(['property:id,host_user_id,user_id'])
            ->where('room_id', $this->roomId)
            ->where('property_id', $this->propertyId)
            ->findOrFail($this->sleepingPlaceId);

        abort_unless($sleepingPlace->property?->isOwnedBy(auth()->user()) === true, 403);

        return $sleepingPlace;
    }

    private function translatedLookupLabel($translations, string $fallback): string
    {
        $locale = app()->getLocale();
        $translation = $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', config('app.fallback_locale'));

        return $translation?->name ?: __('host.lookup_fallback.item');
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function integerIds(array $ids): array
    {
        return collect($ids)
            ->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
