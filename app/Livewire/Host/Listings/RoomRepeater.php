<?php

namespace App\Livewire\Host\Listings;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\Catalog\AmenityRuleCatalog;
use App\Services\Catalog\AmenityRuleLookupService;
use App\Services\HostListings\Wizard\HostRoomDraftService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoomRepeater extends Component
{
    #[Locked]
    public int $propertyId;

    /**
     * @var list<array{id:int,title:string,type:string,sleeping_places_count:int,living_format:string,description:string,room_rules_text:string,rules:list<string>,status:string,gender_policy:string,sleeping_places_total:int,media_count:int}>
     */
    public array $rooms = [];

    public function mount(int $propertyId): void
    {
        $this->propertyId = $this->ownedProperty($propertyId)->id;
        $this->loadRooms();
    }

    public function addRoom(HostRoomDraftService $rooms): void
    {
        $host = auth()->user();
        $property = $this->ownedProperty($this->propertyId);

        abort_unless($host instanceof User, 403);

        $nextNumber = $property->rooms()->count() + 1;

        $rooms->createRoomForHost($host, $property, [
            'title' => __('listing_wizard.defaults.room_title').' '.$nextNumber,
            'room_number' => (string) $nextNumber,
            'sleeping_places_count' => 1,
        ]);

        $this->loadRooms();
        $this->dispatch('listing-step-saved');
    }

    public function saveRoom(int $index, HostRoomDraftService $rooms): void
    {
        abort_unless(isset($this->rooms[$index]), 404);

        $host = auth()->user();
        $property = $this->ownedProperty($this->propertyId);

        abort_unless($host instanceof User, 403);

        $validated = $this->validate($this->rulesFor($index), attributes: $this->validationAttributes());
        $row = $validated['rooms'][$index];
        $room = $property->rooms()->whereKey($row['id'])->firstOrFail();

        $rooms->updateRoomForHost($host, $room, [
            'title' => str($row['title'])->squish()->toString(),
            'type' => $row['type'],
            'room_type' => $row['type'],
            'sleeping_places_count' => (int) $row['sleeping_places_count'],
            'living_format' => $row['living_format'],
            'description' => str($row['description'] ?? '')->squish()->toString(),
            'room_rules_text' => str($row['room_rules_text'] ?? '')->squish()->toString(),
            'rules' => array_values($row['rules'] ?? []),
            'status' => $row['status'],
            'gender_policy' => $row['gender_policy'],
        ]);

        $this->loadRooms();
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.room-repeater', [
            'roomTypeOptions' => $this->roomTypeOptions(),
            'livingFormatOptions' => $this->livingFormatOptions(),
            'genderPolicyOptions' => $this->genderPolicyOptions(),
            'statusOptions' => $this->statusOptions(),
            'ruleOptions' => $this->ruleOptions(),
        ]);
    }

    private function loadRooms(): void
    {
        $property = $this->ownedProperty($this->propertyId);

        $this->rooms = $property->rooms()
            ->select([
                'id',
                'property_id',
                'user_id',
                'title',
                'type',
                'room_type',
                'sleeping_places_count',
                'living_format',
                'description',
                'room_rules_text',
                'rules',
                'status',
                'gender_policy',
                'sort_order',
            ])
            ->withCount([
                'sleepingPlaces as created_sleeping_places_count',
                'mediaItems as media_items_count',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'title' => (string) $room->title,
                'type' => $room->type?->value ?? RoomType::Shared->value,
                'sleeping_places_count' => max(1, (int) $room->sleeping_places_count),
                'living_format' => $room->living_format ?: 'shared',
                'description' => (string) $room->description,
                'room_rules_text' => (string) $room->room_rules_text,
                'rules' => $this->storedList($room->rules),
                'status' => $room->status?->value ?? RoomStatus::Draft->value,
                'gender_policy' => $room->gender_policy?->value ?? GenderType::Mixed->value,
                'sleeping_places_total' => (int) $room->created_sleeping_places_count,
                'media_count' => (int) $room->media_items_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesFor(int $index): array
    {
        return [
            'rooms.'.$index.'.id' => ['required', 'integer', ValidationRule::exists('rooms', 'id')],
            'rooms.'.$index.'.title' => ['required', 'string', 'min:2', 'max:120'],
            'rooms.'.$index.'.type' => ['required', ValidationRule::in($this->roomTypeValues())],
            'rooms.'.$index.'.sleeping_places_count' => ['required', 'integer', 'min:1', 'max:40'],
            'rooms.'.$index.'.living_format' => ['required', ValidationRule::in(array_keys($this->livingFormatOptions()))],
            'rooms.'.$index.'.description' => ['nullable', 'string', 'max:1500'],
            'rooms.'.$index.'.room_rules_text' => ['nullable', 'string', 'max:1200'],
            'rooms.'.$index.'.rules' => ['array', 'max:8'],
            'rooms.'.$index.'.rules.*' => ['string', ValidationRule::in($this->ruleValues())],
            'rooms.'.$index.'.status' => ['required', ValidationRule::in($this->statusValues())],
            'rooms.'.$index.'.gender_policy' => ['required', ValidationRule::in($this->genderValues())],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'rooms.*.title' => __('listing_wizard.rooms.name'),
            'rooms.*.type' => __('listing_wizard.rooms.type'),
            'rooms.*.sleeping_places_count' => __('listing_wizard.rooms.sleeping_places_count'),
            'rooms.*.living_format' => __('listing_wizard.rooms.living_format'),
            'rooms.*.description' => __('listing_wizard.rooms.description'),
            'rooms.*.room_rules_text' => __('listing_wizard.rooms.rules'),
            'rooms.*.rules' => __('listing_wizard.rooms.rules'),
            'rooms.*.status' => __('listing_wizard.rooms.status'),
            'rooms.*.gender_policy' => __('listing_wizard.rooms.gender_policy'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function roomTypeOptions(): array
    {
        return collect(RoomType::cases())
            ->mapWithKeys(fn (RoomType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function livingFormatOptions(): array
    {
        return [
            'shared' => __('listing_wizard.rooms.living_formats.shared'),
            'private' => __('listing_wizard.rooms.living_formats.private'),
            'family' => __('listing_wizard.rooms.living_formats.family'),
            'workers' => __('listing_wizard.rooms.living_formats.workers'),
            'students' => __('listing_wizard.rooms.living_formats.students'),
            'long_stay' => __('listing_wizard.rooms.living_formats.long_stay'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function genderPolicyOptions(): array
    {
        return collect(GenderType::cases())
            ->mapWithKeys(fn (GenderType $gender): array => [$gender->value => $gender->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(RoomStatus::cases())
            ->mapWithKeys(fn (RoomStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function ruleOptions(): array
    {
        $lookup = app(AmenityRuleLookupService::class)->ruleOptions(
            locale: app()->getLocale(),
            categories: ['quiet_hours', 'shared_room_behavior', 'cleanliness', 'visitors', 'security'],
            limit: 18,
        );

        if ($lookup !== []) {
            return collect($lookup)
                ->mapWithKeys(fn (array $option): array => [$option['slug'] => $option['label']])
                ->all();
        }

        $locale = app()->getLocale();

        return collect(AmenityRuleCatalog::rules())
            ->whereIn('category', ['quiet_hours', 'shared_room_behavior', 'cleanliness', 'visitors', 'security'])
            ->take(18)
            ->mapWithKeys(fn (array $option): array => [$option['slug'] => $option[$locale] ?? $option['en']])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function roomTypeValues(): array
    {
        return array_map(fn (RoomType $type): string => $type->value, RoomType::cases());
    }

    /**
     * @return list<string>
     */
    private function statusValues(): array
    {
        return array_map(fn (RoomStatus $status): string => $status->value, RoomStatus::cases());
    }

    /**
     * @return list<string>
     */
    private function genderValues(): array
    {
        return array_map(fn (GenderType $gender): string => $gender->value, GenderType::cases());
    }

    /**
     * @return list<string>
     */
    private function ruleValues(): array
    {
        return collect(AmenityRuleCatalog::rules())->pluck('slug')->all();
    }

    private function ownedProperty(int $propertyId): Property
    {
        $property = Property::query()
            ->select(['id', 'host_user_id', 'user_id'])
            ->findOrFail($propertyId);

        $host = auth()->user();
        abort_unless($host instanceof User && $property->isOwnedBy($host), 403);

        return $property;
    }

    /**
     * @return list<string>
     */
    private function storedList(mixed $value): array
    {
        return collect(is_array($value) ? $value : [])
            ->filter(fn (mixed $item): bool => is_string($item) && $item !== '')
            ->values()
            ->all();
    }
}
