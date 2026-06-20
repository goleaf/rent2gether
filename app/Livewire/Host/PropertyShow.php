<?php

namespace App\Livewire\Host;

use App\Actions\Rooms\DuplicateRoomAction;
use App\Actions\Rooms\GenerateSleepingPlaceDraftsAction;
use App\Enums\RoomStatus;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\HostListings\HostListingDashboardService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\Localization\SupportedContentLocales;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PropertyShow extends Component
{
    public int $propertyId;

    public function mount(Property $property): void
    {
        abort_unless($property->isOwnedBy(auth()->user()), 403);

        $this->propertyId = $property->id;
    }

    public function duplicateRoom(int $roomId): void
    {
        $room = $this->ownedRoomQuery()
            ->with(['property', 'translations', 'rules'])
            ->findOrFail($roomId);

        app(DuplicateRoomAction::class)->handle($room, auth()->user());

        unset($this->rooms);
        session()->flash('success', __('notifications.flash.room_duplicated'));
    }

    public function deleteDraftRoom(int $roomId): void
    {
        $room = $this->ownedRoomQuery()->findOrFail($roomId);

        if ($room->status !== RoomStatus::Draft) {
            session()->flash('success', __('notifications.flash.room_delete_only_draft'));

            return;
        }

        $room->delete();

        unset($this->rooms);
        session()->flash('success', __('notifications.flash.room_deleted'));
    }

    public function generateSleepingPlaces(int $roomId): void
    {
        $room = $this->ownedRoomQuery()->findOrFail($roomId);
        $created = app(GenerateSleepingPlaceDraftsAction::class)->handle($room);

        unset($this->rooms);

        session()->flash('success', $created > 0
            ? trans_choice('notifications.flash.sleeping_places_generated', $created, ['count' => $created])
            : __('notifications.flash.sleeping_places_already_ready'));
    }

    #[Computed]
    public function property(): Property
    {
        $property = Property::query()
            ->select(['id', 'host_user_id', 'user_id', 'title', 'city', 'country'])
            ->with(['translations:id,property_id,locale,title'])
            ->findOrFail($this->propertyId);

        abort_unless($property->isOwnedBy(auth()->user()), 403);

        return $property;
    }

    #[Computed]
    public function propertyDisplay(): array
    {
        $property = $this->property;
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $property->translations,
            app()->getLocale(),
            'en',
        );

        return [
            'title' => $translation?->title ?: $property->title,
            'location' => collect([$property->city, $property->country])->filter()->implode(', '),
        ];
    }

    #[Computed]
    public function rooms(): array
    {
        $resolver = app(LocalizedModelContentResolver::class);
        $contentLocales = app(SupportedContentLocales::class);

        return $this->ownedRoomQuery()
            ->with([
                'translations:id,room_id,locale,title,description,notes,privacy_notes',
                'rules:id',
                'mediaItems:id,mediable_type,mediable_id,collection',
                'sleepingPlaces:id,room_id,status,place_number,type',
            ])
            ->orderBy('room_number')
            ->orderBy('id')
            ->get()
            ->map(function (Room $room) use ($resolver, $contentLocales): array {
                $translation = $resolver->resolve($room->translations, app()->getLocale(), 'en');
                $descriptionReady = $contentLocales->hasAllTranslations($room->translations, ['description']);
                $photosCount = $room->mediaItems->count();
                $placesCount = $room->sleepingPlaces->count();
                $rulesCount = $room->getRelation('rules')->count();

                return [
                    'id' => $room->id,
                    'title' => $translation?->title ?: $room->title,
                    'description' => $translation?->description,
                    'notes' => $translation?->notes ?: $translation?->privacy_notes,
                    'status' => $room->status?->value ?? (string) $room->status,
                    'status_label' => $room->status?->label() ?? (string) $room->status,
                    'type_label' => $room->type?->label() ?? '',
                    'gender_label' => ($room->gender_policy ?: $room->gender_type)?->label() ?? '',
                    'room_number' => $room->room_number,
                    'area' => $room->area ?: $room->area_sqm,
                    'max_guests' => $room->max_guests ?: $room->capacity,
                    'beds_count' => (int) $room->beds_count,
                    'sleeping_places_count' => $placesCount,
                    'needs_sleeping_places' => (int) $room->beds_count > $placesCount,
                    'can_delete_draft' => $room->status === RoomStatus::Draft,
                    'readiness' => [
                        [
                            'label' => __('host.room_wizard.readiness.description'),
                            'done' => $descriptionReady,
                        ],
                        [
                            'label' => __('host.room_wizard.readiness.photos'),
                            'done' => $photosCount > 0,
                        ],
                        [
                            'label' => __('host.room_wizard.readiness.sleeping_places'),
                            'done' => $placesCount > 0,
                        ],
                        [
                            'label' => __('host.room_wizard.readiness.rules'),
                            'done' => $rulesCount > 0 || filled($room->room_rules_text),
                        ],
                    ],
                ];
            })
            ->all();
    }

    public function render(HostListingDashboardService $dashboard): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return view('livewire.host.property-show', [
            'propertySummary' => $dashboard->propertyDetail($user, $this->property),
        ])
            ->layout('layouts.app', ['title' => __('host.rooms.title')]);
    }

    private function ownedRoomQuery()
    {
        return Room::query()
            ->select([
                'id',
                'property_id',
                'title',
                'gender_type',
                'description',
                'capacity',
                'area_sqm',
                'has_lock',
                'has_window',
                'has_wardrobe',
                'has_desk',
                'has_ac',
                'has_heating',
                'has_balcony',
                'rules',
                'room_rules_text',
                'status',
                'type',
                'is_private',
                'is_pass_through',
                'room_number',
                'floor',
                'area',
                'beds_count',
                'max_guests',
                'occupied_places_count',
                'available_places_count',
                'gender_policy',
                'min_guest_age',
                'max_guest_age',
                'windows_count',
                'window_view',
                'has_chair',
                'has_mirror',
                'has_air_conditioning',
                'has_curtains',
                'has_blackout_curtains',
                'noise_level',
                'light_level',
                'ventilation_level',
                'can_eat',
                'can_work_at_night',
                'can_turn_light_at_night',
                'can_talk_at_night',
                'created_at',
                'updated_at',
            ])
            ->where('property_id', $this->propertyId)
            ->whereHas('property', fn ($query) => $query
                ->where('host_user_id', auth()->id())
                ->orWhere('user_id', auth()->id()));
    }
}
