<?php

namespace App\Livewire\Host;

use App\Actions\Media\DeleteMediaItemAction;
use App\Actions\Media\StoreMediaItemAction;
use App\Actions\Rooms\GenerateSleepingPlaceDraftsAction;
use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Livewire\Concerns\BuildsLocalizedMediaCaptions;
use App\Livewire\Concerns\ManagesLocalizedFormTranslations;
use App\Livewire\Host\Concerns\BuildsWizardPhotoPreviews;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Room;
use App\Services\Catalog\AmenityRuleLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class RoomForm extends Component
{
    use BuildsLocalizedMediaCaptions;
    use BuildsWizardPhotoPreviews;
    use ManagesLocalizedFormTranslations;
    use WithFileUploads;

    private const STEP_COUNT = 6;

    private const PHOTO_FIELDS = [
        'roomPhoto' => 'room',
        'windowPhoto' => 'window',
        'detailPhoto' => 'detail',
    ];

    private const TRANSLATION_FIELDS = [
        'title',
        'description',
        'notes',
    ];

    public ?int $propertyId = null;

    public ?int $roomId = null;

    public int $step = 1;

    public bool $wasSaved = false;

    public string $roomNumber = '';

    public string $title = '';

    public string $roomType = 'shared';

    public string $genderPolicy = 'mixed';

    public string $status = 'draft';

    public bool $isPrivate = false;

    public bool $isPassThrough = false;

    public ?float $area = null;

    public ?int $floor = null;

    public ?int $windowsCount = null;

    public string $windowView = '';

    public bool $hasLock = false;

    public bool $hasWindow = true;

    public bool $hasWardrobe = false;

    public bool $hasDesk = false;

    public bool $hasChair = false;

    public bool $hasMirror = false;

    public bool $hasHeating = true;

    public bool $hasAirConditioning = false;

    public bool $hasBalcony = false;

    public bool $hasCurtains = false;

    public bool $hasBlackoutCurtains = false;

    public string $noiseLevel = '';

    public string $lightLevel = '';

    public string $ventilationLevel = '';

    public ?int $maxGuests = 1;

    public ?int $bedsCount = 0;

    public ?int $minGuestAge = null;

    public ?int $maxGuestAge = null;

    public bool $canEat = false;

    public bool $canWorkAtNight = false;

    public bool $canUseLightAtNight = false;

    public bool $canTalkAtNight = false;

    public string $roomRulesText = '';

    /** @var list<int> */
    public array $ruleIds = [];

    public bool $generateSleepingPlacesAfterSave = false;

    public $roomPhoto = null;

    public $windowPhoto = null;

    public $detailPhoto = null;

    public function mount(Property $property, ?Room $room = null): void
    {
        $this->fillBlankTranslations(self::TRANSLATION_FIELDS);

        abort_unless($property->isOwnedBy(auth()->user()), 403);

        if ($room?->exists) {
            abort_unless((int) $room->property_id === (int) $property->id, 404);
        }

        $this->propertyId = $property->id;

        if (! $room?->exists) {
            return;
        }

        $room->load(['translations', 'rules:id']);

        $this->roomId = $room->id;
        $this->roomNumber = $room->room_number ?: '';
        $this->title = $room->title;
        $this->roomType = $room->type?->value ?? RoomType::Shared->value;
        $this->genderPolicy = $room->gender_policy?->value ?? $room->gender_type?->value ?? GenderType::Mixed->value;
        $this->status = $room->status?->value ?? RoomStatus::Draft->value;
        $this->isPrivate = (bool) $room->is_private;
        $this->isPassThrough = (bool) $room->is_pass_through;
        $this->area = $room->area === null ? ($room->area_sqm === null ? null : (float) $room->area_sqm) : (float) $room->area;
        $this->floor = $room->floor;
        $this->windowsCount = $room->windows_count;
        $this->windowView = $room->window_view ?: '';
        $this->hasLock = (bool) $room->has_lock;
        $this->hasWindow = (bool) $room->has_window;
        $this->hasWardrobe = (bool) $room->has_wardrobe;
        $this->hasDesk = (bool) $room->has_desk;
        $this->hasChair = (bool) $room->has_chair;
        $this->hasMirror = (bool) $room->has_mirror;
        $this->hasHeating = (bool) $room->has_heating;
        $this->hasAirConditioning = (bool) ($room->has_air_conditioning || $room->has_ac);
        $this->hasBalcony = (bool) $room->has_balcony;
        $this->hasCurtains = (bool) $room->has_curtains;
        $this->hasBlackoutCurtains = (bool) $room->has_blackout_curtains;
        $this->noiseLevel = $room->noise_level ?: '';
        $this->lightLevel = $room->light_level === 'normal' ? 'moderate' : ($room->light_level ?: '');
        $this->ventilationLevel = $room->ventilation_level ?: '';
        $this->maxGuests = $room->max_guests ?: $room->capacity ?: 1;
        $this->bedsCount = $room->beds_count ?: 0;
        $this->minGuestAge = $room->min_guest_age;
        $this->maxGuestAge = $room->max_guest_age;
        $this->canEat = (bool) $room->can_eat;
        $this->canWorkAtNight = (bool) $room->can_work_at_night;
        $this->canUseLightAtNight = (bool) $room->can_turn_light_at_night;
        $this->canTalkAtNight = (bool) $room->can_talk_at_night;
        $this->roomRulesText = $room->room_rules_text ?: '';
        $this->ruleIds = $room->getRelation('rules')->pluck('id')->map(fn (int $id): int => $id)->all();

        $this->loadLocalizedTranslations($room->translations, self::TRANSLATION_FIELDS);
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

        $room = $this->persistRoom($this->intermediateStatus());
        $this->syncStepRelations($room, $this->step);
        $this->wasSaved = true;
    }

    public function save()
    {
        return $this->publish();
    }

    public function publish()
    {
        $this->validate($this->allRules(), attributes: $this->validationAttributes());

        $room = $this->persistRoom($this->status ?: RoomStatus::Draft->value);
        $this->syncRules($room);
        $this->syncPhotos($room);

        if ($this->generateSleepingPlacesAfterSave && (int) $this->bedsCount > 0) {
            $created = app(GenerateSleepingPlaceDraftsAction::class)->handle($room);

            if ($created > 0) {
                session()->flash('success', trans_choice('notifications.flash.sleeping_places_generated', $created, ['count' => $created]));
            }
        } else {
            session()->flash('success', $this->roomId
                ? __('notifications.flash.room_updated')
                : __('notifications.flash.room_created'));
        }

        return $this->redirect(route('host.properties.show', [
            'locale' => app()->getLocale(),
            'property' => $room->property_id,
        ]), navigate: true);
    }

    /**
     * @return array<string, array{url:string,caption:string}>
     */
    #[Computed]
    public function savedPhotoPreviews(): array
    {
        $room = $this->roomModel();

        if (! $room) {
            return [];
        }

        return $room->mediaItems()
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
                'title' => __('host.room_wizard.steps.'.$step.'.title'),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function roomTypeOptions(): array
    {
        return collect(RoomType::cases())
            ->mapWithKeys(fn (RoomType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function genderPolicyOptions(): array
    {
        return collect(GenderType::cases())
            ->mapWithKeys(fn (GenderType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return collect([
            RoomStatus::Draft,
            RoomStatus::Active,
            RoomStatus::Hidden,
            RoomStatus::Unavailable,
        ])->mapWithKeys(fn (RoomStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function levelOptions(string $group): array
    {
        return collect(['low', 'moderate', 'good', 'high'])
            ->mapWithKeys(fn (string $value): array => [$value => __('host.room_wizard.options.'.$group.'.'.$value)])
            ->all();
    }

    /**
     * @return list<array{key:string,label:string,done:bool}>
     */
    public function readinessChecklist(): array
    {
        $room = $this->roomModel();
        $photoCount = $room?->mediaItems()->count() ?? 0;
        $sleepingPlaceCount = $room?->sleepingPlaces()->count() ?? 0;
        $rulesCount = $room?->rules()->count() ?? 0;

        return [
            [
                'key' => 'description',
                'label' => __('host.room_wizard.readiness.description'),
                'done' => $this->hasEveryLocaleValue('description'),
            ],
            [
                'key' => 'photos',
                'label' => __('host.room_wizard.readiness.photos'),
                'done' => $photoCount > 0 || $this->hasPendingPhoto(),
            ],
            [
                'key' => 'sleeping_places',
                'label' => __('host.room_wizard.readiness.sleeping_places'),
                'done' => $sleepingPlaceCount > 0 || ($this->generateSleepingPlacesAfterSave && (int) $this->bedsCount > 0),
            ],
            [
                'key' => 'rules',
                'label' => __('host.room_wizard.readiness.rules'),
                'done' => $rulesCount > 0 || $this->ruleIds !== [] || trim($this->roomRulesText) !== '',
            ],
        ];
    }

    #[Computed]
    public function property(): Property
    {
        return Property::query()
            ->select(['id', 'host_user_id', 'user_id', 'title', 'city', 'country'])
            ->findOrFail($this->propertyId);
    }

    #[Computed]
    public function ruleOptions(): array
    {
        return app(AmenityRuleLookupService::class)->ruleOptions(
            locale: app()->getLocale(),
            categories: ['quiet_hours', 'shared_room_behavior', 'cleanliness', 'visitors', 'security'],
        );
    }

    public function render(): View
    {
        return view('livewire.host.room-form')
            ->layout('layouts.app', ['title' => __('host.room_wizard.title')]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'roomNumber' => ['nullable', 'string', 'max:50'],
                'title' => ['required', 'string', 'max:255'],
                'roomType' => ['required', ValidationRule::enum(RoomType::class)],
                'genderPolicy' => ['required', ValidationRule::enum(GenderType::class)],
                'status' => ['required', ValidationRule::in([
                    RoomStatus::Draft->value,
                    RoomStatus::Active->value,
                    RoomStatus::Hidden->value,
                    RoomStatus::Unavailable->value,
                ])],
                'isPrivate' => ['boolean'],
                'isPassThrough' => ['boolean'],
            ],
            2 => [
                'area' => ['nullable', 'numeric', 'min:1', 'max:1000'],
                'floor' => ['nullable', 'integer', 'min:0', 'max:200'],
                'windowsCount' => ['nullable', 'integer', 'min:0', 'max:20'],
                'windowView' => ['nullable', 'string', 'max:120'],
                'hasLock' => ['boolean'],
                'hasWindow' => ['boolean'],
                'hasWardrobe' => ['boolean'],
                'hasDesk' => ['boolean'],
                'hasChair' => ['boolean'],
                'hasMirror' => ['boolean'],
                'hasHeating' => ['boolean'],
                'hasAirConditioning' => ['boolean'],
                'hasBalcony' => ['boolean'],
                'hasCurtains' => ['boolean'],
                'hasBlackoutCurtains' => ['boolean'],
            ],
            3 => [
                'noiseLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'lightLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'ventilationLevel' => ['nullable', ValidationRule::in(['low', 'moderate', 'good', 'high'])],
                'maxGuests' => ['required', 'integer', 'min:1', 'max:100'],
                'bedsCount' => ['nullable', 'integer', 'min:0', 'max:100'],
                'minGuestAge' => ['nullable', 'integer', 'min:0', 'max:120'],
                'maxGuestAge' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:minGuestAge'],
                'canEat' => ['boolean'],
                'canWorkAtNight' => ['boolean'],
                'canUseLightAtNight' => ['boolean'],
                'canTalkAtNight' => ['boolean'],
            ],
            4 => $this->localizedTranslationRules([
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:4000'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]),
            5 => [
                'ruleIds' => ['array'],
                'ruleIds.*' => ['integer', 'exists:rules,id'],
                'roomRulesText' => ['nullable', 'string', 'max:2000'],
            ],
            6 => $this->photoRules(),
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
                $field => ['nullable', 'image', 'max:2048', 'dimensions:max_width=2400,max_height=2400'],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('host.room_wizard.validation_attributes');

        return array_merge(
            is_array($attributes) ? $attributes : [],
            $this->localizedValidationAttributes('host.room_wizard.translation_fields', self::TRANSLATION_FIELDS),
        );
    }

    private function persistRoom(string $status): Room
    {
        $property = $this->propertyModel();
        $fallbackTitle = $this->title ?: $this->firstTranslationValue('title') ?: __('host.room_wizard.draft_title');
        $bedsCount = (int) ($this->bedsCount ?? 0);
        $maxGuests = (int) ($this->maxGuests ?? 1);

        $data = [
            'property_id' => $property->id,
            'title' => $fallbackTitle,
            'gender_type' => $this->genderPolicy,
            'gender_policy' => $this->genderPolicy,
            'description' => $this->blankToNull($this->firstTranslationValue('description')),
            'capacity' => $maxGuests,
            'area_sqm' => $this->area,
            'area' => $this->area,
            'has_lock' => $this->hasLock,
            'has_window' => $this->hasWindow,
            'has_wardrobe' => $this->hasWardrobe,
            'has_desk' => $this->hasDesk,
            'has_ac' => $this->hasAirConditioning,
            'has_heating' => $this->hasHeating,
            'has_balcony' => $this->hasBalcony,
            'status' => $status,
            'type' => $this->roomType,
            'is_private' => $this->isPrivate,
            'is_pass_through' => $this->isPassThrough,
            'room_number' => $this->blankToNull($this->roomNumber),
            'floor' => $this->floor,
            'beds_count' => $bedsCount,
            'max_guests' => $maxGuests,
            'available_places_count' => max(0, $bedsCount),
            'min_guest_age' => $this->minGuestAge,
            'max_guest_age' => $this->maxGuestAge,
            'windows_count' => $this->windowsCount ?: 0,
            'window_view' => $this->blankToNull($this->windowView),
            'has_chair' => $this->hasChair,
            'has_mirror' => $this->hasMirror,
            'has_air_conditioning' => $this->hasAirConditioning,
            'has_curtains' => $this->hasCurtains,
            'has_blackout_curtains' => $this->hasBlackoutCurtains,
            'noise_level' => $this->blankToNull($this->noiseLevel),
            'light_level' => $this->blankToNull($this->lightLevel),
            'ventilation_level' => $this->blankToNull($this->ventilationLevel),
            'can_eat' => $this->canEat,
            'can_work_at_night' => $this->canWorkAtNight,
            'can_turn_light_at_night' => $this->canUseLightAtNight,
            'can_talk_at_night' => $this->canTalkAtNight,
            'room_rules_text' => $this->blankToNull($this->roomRulesText),
        ];

        $room = $this->roomModel();

        if ($room) {
            $room->update($data);
        } else {
            $room = Room::query()->create($data);
            $this->roomId = $room->id;
        }

        $this->syncTranslations($room);

        return $room->refresh();
    }

    private function syncStepRelations(Room $room, int $step): void
    {
        if ($step >= 5) {
            $this->syncRules($room);
        }

        if ($step >= 6) {
            $this->syncPhotos($room);
        }
    }

    private function syncRules(Room $room): void
    {
        $room->rules()->sync($this->integerIds($this->ruleIds));
    }

    private function syncTranslations(Room $room): void
    {
        foreach ($this->contentLocales() as $localeData) {
            $locale = $localeData['code'];
            $translation = $this->translations[$locale] ?? [];
            $title = $this->blankToNull($translation['title'] ?? '') ?: $this->blankToNull($this->title);

            if (! $title) {
                continue;
            }

            $room->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $title,
                    'summary' => null,
                    'description' => $this->blankToNull($translation['description']),
                    'notes' => $this->blankToNull($translation['notes']),
                    'privacy_notes' => $this->blankToNull($translation['notes']),
                ],
            );
        }
    }

    private function syncPhotos(Room $room): void
    {
        $store = app(StoreMediaItemAction::class);
        $delete = app(DeleteMediaItemAction::class);

        foreach (self::PHOTO_FIELDS as $field => $collection) {
            $photo = $this->{$field};

            if (! $photo instanceof UploadedFile) {
                continue;
            }

            $room->mediaItems()
                ->where('collection', $collection)
                ->get()
                ->each(function ($media) use ($delete): void {
                    $delete->handle($media);
                });

            $store->handle(
                owner: $room,
                file: $photo,
                user: auth()->user(),
                collection: $collection,
                captions: $this->localizedCaptions('host.room_wizard.photos.'.$collection),
                makePrimary: $collection === 'room',
            );

            $this->{$field} = null;
        }
    }

    private function intermediateStatus(): string
    {
        if (! $this->roomId) {
            return RoomStatus::Draft->value;
        }

        return $this->status ?: RoomStatus::Draft->value;
    }

    private function propertyModel(): Property
    {
        $property = Property::query()->findOrFail($this->propertyId);

        abort_unless($property->isOwnedBy(auth()->user()), 403);

        return $property;
    }

    private function roomModel(): ?Room
    {
        if (! $this->roomId) {
            return null;
        }

        $room = Room::query()
            ->with(['property:id,host_user_id,user_id'])
            ->where('property_id', $this->propertyId)
            ->findOrFail($this->roomId);

        abort_unless($room->property?->isOwnedBy(auth()->user()) === true, 403);

        return $room;
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

    private function hasPendingPhoto(): bool
    {
        foreach (array_keys(self::PHOTO_FIELDS) as $field) {
            if ($this->{$field} instanceof UploadedFile) {
                return true;
            }
        }

        return false;
    }
}
