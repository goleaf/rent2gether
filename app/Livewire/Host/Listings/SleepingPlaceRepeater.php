<?php

namespace App\Livewire\Host\Listings;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\Wizard\HostSleepingPlaceDraftService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlaceRepeater extends Component
{
    #[Locked]
    public int $roomId;

    public string $roomTitle = '';

    /**
     * @var list<array{id:int,place_number:string,type:string,base_price_per_night:string,availability:string,features:list<string>,status:string,media_count:int}>
     */
    public array $places = [];

    public function mount(int $roomId): void
    {
        $room = $this->ownedRoom($roomId);

        $this->roomId = $room->id;
        $this->roomTitle = (string) $room->title;
        $this->loadPlaces();
    }

    public function autoCreate(HostSleepingPlaceDraftService $places): void
    {
        $host = auth()->user();
        $room = $this->ownedRoom($this->roomId);

        abort_unless($host instanceof User, 403);

        $places->autoCreatePlacesForRoomForHost($host, $room, max(1, (int) $room->sleeping_places_count));

        $this->loadPlaces();
        $this->dispatch('listing-step-saved');
    }

    public function addPlace(HostSleepingPlaceDraftService $places): void
    {
        $host = auth()->user();
        $room = $this->ownedRoom($this->roomId);

        abort_unless($host instanceof User, 403);

        $nextNumber = $room->sleepingPlaces()->count() + 1;

        $places->createSleepingPlaceForHost($host, $room, [
            'place_number' => (string) $nextNumber,
            'display_name' => __('listing_wizard.defaults.sleeping_place_name', ['number' => $nextNumber]),
            'sort_order' => $nextNumber,
        ]);

        $this->loadPlaces();
        $this->dispatch('listing-step-saved');
    }

    public function savePlace(int $index, HostSleepingPlaceDraftService $places): void
    {
        abort_unless(isset($this->places[$index]), 404);

        $host = auth()->user();
        $room = $this->ownedRoom($this->roomId);

        abort_unless($host instanceof User, 403);

        $validated = $this->validate($this->rulesFor($index), attributes: $this->validationAttributes());
        $row = $validated['places'][$index];
        $place = $room->sleepingPlaces()->whereKey($row['id'])->firstOrFail();

        $places->updateSleepingPlaceForHost($host, $place, [
            'place_number' => str($row['place_number'])->squish()->toString(),
            'display_name' => __('listing_wizard.defaults.sleeping_place_name', [
                'number' => str($row['place_number'])->squish()->toString(),
            ]),
            'type' => $row['type'],
            'sleeping_place_type' => $row['type'],
            'base_price_per_night' => $row['base_price_per_night'],
            'currency' => 'EUR',
            'status' => $row['status'],
            ...$this->availabilityPayload($row['availability']),
            ...$this->featurePayload($row['features'] ?? []),
        ]);

        $this->loadPlaces();
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.sleeping-place-repeater', [
            'typeOptions' => $this->typeOptions(),
            'availabilityOptions' => $this->availabilityOptions(),
            'featureOptions' => $this->featureOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    private function loadPlaces(): void
    {
        $room = $this->ownedRoom($this->roomId);
        $this->roomTitle = (string) $room->title;

        $this->places = $room->sleepingPlaces()
            ->select([
                'id',
                'room_id',
                'property_id',
                'user_id',
                'type',
                'sleeping_place_type',
                'status',
                'place_number',
                'display_name',
                'base_price_per_night',
                'currency',
                'has_power_socket',
                'has_lamp',
                'has_locker',
                'has_curtain',
                'has_bedding',
                'has_towel',
                'has_luggage_space',
                'instant_booking_enabled',
                'requires_host_approval',
                'sort_order',
            ])
            ->with(['calendarSettings:id,sleeping_place_id,booking_mode,request_only,requires_host_confirmation'])
            ->withCount(['mediaItems as media_items_count'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (SleepingPlace $place): array => [
                'id' => $place->id,
                'place_number' => (string) $place->place_number,
                'type' => $place->type?->value ?? SleepingPlaceType::Single->value,
                'base_price_per_night' => (string) $place->base_price_per_night,
                'availability' => $this->availabilityMode($place),
                'features' => $this->selectedFeatures($place),
                'status' => $place->status?->value ?? SleepingPlaceStatus::Draft->value,
                'media_count' => (int) $place->media_items_count,
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
            'places.'.$index.'.id' => ['required', 'integer', ValidationRule::exists('sleeping_places', 'id')],
            'places.'.$index.'.place_number' => ['required', 'string', 'max:40'],
            'places.'.$index.'.type' => ['required', ValidationRule::in($this->typeValues())],
            'places.'.$index.'.base_price_per_night' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'places.'.$index.'.availability' => ['required', ValidationRule::in(array_keys($this->availabilityOptions()))],
            'places.'.$index.'.features' => ['array', 'max:8'],
            'places.'.$index.'.features.*' => ['string', ValidationRule::in(array_keys($this->featureOptions()))],
            'places.'.$index.'.status' => ['required', ValidationRule::in($this->statusValues())],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'places.*.place_number' => __('listing_wizard.sleeping_places.place_number'),
            'places.*.type' => __('listing_wizard.sleeping_places.type'),
            'places.*.base_price_per_night' => __('listing_wizard.sleeping_places.price'),
            'places.*.availability' => __('listing_wizard.sleeping_places.availability'),
            'places.*.features' => __('listing_wizard.sleeping_places.features'),
            'places.*.status' => __('listing_wizard.sleeping_places.status'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function typeOptions(): array
    {
        return collect(SleepingPlaceType::cases())
            ->mapWithKeys(fn (SleepingPlaceType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function availabilityOptions(): array
    {
        return [
            'instant' => __('listing_wizard.sleeping_places.availability_modes.instant'),
            'host_confirmation' => __('listing_wizard.sleeping_places.availability_modes.host_confirmation'),
            'request_only' => __('listing_wizard.sleeping_places.availability_modes.request_only'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function featureOptions(): array
    {
        return [
            'power_socket' => __('listing_wizard.sleeping_places.feature_options.power_socket'),
            'lamp' => __('listing_wizard.sleeping_places.feature_options.lamp'),
            'locker' => __('listing_wizard.sleeping_places.feature_options.locker'),
            'curtain' => __('listing_wizard.sleeping_places.feature_options.curtain'),
            'bedding' => __('listing_wizard.sleeping_places.feature_options.bedding'),
            'towel' => __('listing_wizard.sleeping_places.feature_options.towel'),
            'luggage_space' => __('listing_wizard.sleeping_places.feature_options.luggage_space'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(SleepingPlaceStatus::cases())
            ->mapWithKeys(fn (SleepingPlaceStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function typeValues(): array
    {
        return array_map(fn (SleepingPlaceType $type): string => $type->value, SleepingPlaceType::cases());
    }

    /**
     * @return list<string>
     */
    private function statusValues(): array
    {
        return array_map(fn (SleepingPlaceStatus $status): string => $status->value, SleepingPlaceStatus::cases());
    }

    /**
     * @return array{instant_booking_enabled:bool,requires_host_approval:bool}
     */
    private function availabilityPayload(string $mode): array
    {
        return match ($mode) {
            'instant' => ['booking_mode' => 'instant', 'instant_booking_enabled' => true, 'requires_host_approval' => false],
            'request_only' => ['booking_mode' => 'request_only', 'instant_booking_enabled' => false, 'requires_host_approval' => true],
            default => ['booking_mode' => 'host_confirmation', 'instant_booking_enabled' => false, 'requires_host_approval' => true],
        };
    }

    /**
     * @param  list<string>  $features
     * @return array<string, bool>
     */
    private function featurePayload(array $features): array
    {
        return [
            'has_power_socket' => in_array('power_socket', $features, true),
            'has_lamp' => in_array('lamp', $features, true),
            'has_locker' => in_array('locker', $features, true),
            'has_curtain' => in_array('curtain', $features, true),
            'has_bedding' => in_array('bedding', $features, true),
            'has_towel' => in_array('towel', $features, true),
            'has_luggage_space' => in_array('luggage_space', $features, true),
        ];
    }

    private function availabilityMode(SleepingPlace $place): string
    {
        if ($place->calendarSettings?->request_only) {
            return 'request_only';
        }

        if ($place->calendarSettings?->requires_host_confirmation) {
            return 'host_confirmation';
        }

        if ($place->calendarSettings?->booking_mode === 'request_only') {
            return 'request_only';
        }

        if ($place->instant_booking_enabled && ! $place->requires_host_approval) {
            return 'instant';
        }

        return $place->requires_host_approval ? 'host_confirmation' : 'request_only';
    }

    /**
     * @return list<string>
     */
    private function selectedFeatures(SleepingPlace $place): array
    {
        return collect([
            'power_socket' => (bool) $place->has_power_socket,
            'lamp' => (bool) $place->has_lamp,
            'locker' => (bool) $place->has_locker,
            'curtain' => (bool) $place->has_curtain,
            'bedding' => (bool) $place->has_bedding,
            'towel' => (bool) $place->has_towel,
            'luggage_space' => (bool) $place->has_luggage_space,
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
    }

    private function ownedRoom(int $roomId): Room
    {
        $room = Room::query()
            ->select(['id', 'property_id', 'user_id', 'title', 'sleeping_places_count'])
            ->with(['property:id,host_user_id,user_id'])
            ->findOrFail($roomId);

        $host = auth()->user();
        abort_unless($host instanceof User && $room->property?->isOwnedBy($host), 403);

        return $room;
    }
}
