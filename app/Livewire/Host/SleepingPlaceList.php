<?php

namespace App\Livewire\Host;

use App\Actions\SleepingPlaces\BulkCreateSleepingPlacesAction;
use App\Actions\SleepingPlaces\DuplicateSleepingPlaceAction;
use App\Enums\SleepingPlaceType;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SleepingPlaceList extends Component
{
    public int $roomId;

    public int $bulkCount = 2;

    public string $bulkTitlePrefix = '';

    public string $bulkType = 'single';

    public ?float $bulkBasePrice = null;

    public string $bulkCurrency = 'EUR';

    public int $bulkMinNights = 1;

    public int $bulkMaxGuests = 1;

    public function mount(Room $room): void
    {
        $room->loadMissing('property');

        abort_unless($room->property?->isOwnedBy(auth()->user()), 403);

        $this->roomId = $room->id;
        $this->bulkTitlePrefix = __('host.sleeping_places.default_name');
    }

    public function duplicateSleepingPlace(int $sleepingPlaceId): void
    {
        $sleepingPlace = $this->ownedSleepingPlaceQuery()
            ->with(['property', 'translations', 'rules'])
            ->findOrFail($sleepingPlaceId);

        app(DuplicateSleepingPlaceAction::class)->handle($sleepingPlace, auth()->user());

        unset($this->sleepingPlaces);
        session()->flash('success', __('notifications.flash.sleeping_place_duplicated'));
    }

    public function bulkCreate(): void
    {
        $validated = $this->validate([
            'bulkCount' => ['required', 'integer', 'min:1', 'max:20'],
            'bulkTitlePrefix' => ['required', 'string', 'max:80'],
            'bulkType' => ['required', ValidationRule::enum(SleepingPlaceType::class)],
            'bulkBasePrice' => ['required', 'numeric', 'min:0', 'max:100000'],
            'bulkCurrency' => ['required', 'string', 'size:3'],
            'bulkMinNights' => ['required', 'integer', 'min:1', 'max:365'],
            'bulkMaxGuests' => ['required', 'integer', 'min:1', 'max:10'],
        ], attributes: $this->validationAttributes());

        $created = app(BulkCreateSleepingPlacesAction::class)->handle($this->room, auth()->user(), [
            'count' => $validated['bulkCount'],
            'title_prefix' => $validated['bulkTitlePrefix'],
            'type' => $validated['bulkType'],
            'base_price_per_night' => $validated['bulkBasePrice'],
            'currency' => strtoupper($validated['bulkCurrency']),
            'min_nights' => $validated['bulkMinNights'],
            'max_guests' => $validated['bulkMaxGuests'],
        ]);

        unset($this->sleepingPlaces);
        session()->flash('success', trans_choice('notifications.flash.sleeping_places_generated', $created, ['count' => $created]));
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
    public function sleepingPlaces(): array
    {
        $resolver = app(LocalizedModelContentResolver::class);

        return $this->ownedSleepingPlaceQuery()
            ->with(['translations:id,sleeping_place_id,locale,title,description,special_conditions'])
            ->withCount(['mediaItems', 'rules', 'availabilityDays'])
            ->orderBy('place_number')
            ->orderBy('id')
            ->get()
            ->map(function (SleepingPlace $place) use ($resolver): array {
                $translation = $resolver->resolve($place->translations, app()->getLocale(), 'en');
                $readiness = [
                    [
                        'label' => __('host.sleeping_place_wizard.readiness.title_field'),
                        'done' => $this->hasLocaleTitle($place, 'en') && $this->hasLocaleTitle($place, 'ru'),
                    ],
                    [
                        'label' => __('host.sleeping_place_wizard.readiness.exact_photo'),
                        'done' => $place->media_items_count > 0,
                    ],
                    [
                        'label' => __('host.sleeping_place_wizard.readiness.price'),
                        'done' => (float) $place->base_price_per_night > 0,
                    ],
                    [
                        'label' => __('host.sleeping_place_wizard.readiness.calendar'),
                        'done' => $place->availability_days_count > 0,
                    ],
                    [
                        'label' => __('host.sleeping_place_wizard.readiness.rules'),
                        'done' => $place->rules_count > 0,
                    ],
                ];

                return [
                    'id' => $place->id,
                    'title' => $translation?->title ?: $place->display_name ?: $place->place_number ?: __('host.sleeping_places.default_name'),
                    'description' => $translation?->description,
                    'special_conditions' => $translation?->special_conditions,
                    'status_label' => $place->status?->label() ?? (string) $place->status,
                    'type_label' => $place->type?->label() ?? '',
                    'place_number' => $place->place_number,
                    'price' => $place->base_price_per_night,
                    'currency' => $place->currency,
                    'max_guests' => $place->max_guests,
                    'min_nights' => $place->min_nights,
                    'readiness_percent' => (int) round((collect($readiness)->where('done', true)->count() / count($readiness)) * 100),
                    'readiness' => $readiness,
                ];
            })
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

    public function render(): View
    {
        return view('livewire.host.sleeping-place-list')
            ->layout('layouts.app', ['title' => __('host.sleeping_places.title')]);
    }

    private function ownedSleepingPlaceQuery()
    {
        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'status',
                'place_number',
                'display_name',
                'bunk_level',
                'length_cm',
                'width_cm',
                'mattress_type',
                'mattress_firmness',
                'has_pillow',
                'has_blanket',
                'has_bedding',
                'has_towel',
                'has_curtain',
                'has_lamp',
                'has_power_socket',
                'has_usb',
                'has_shelf',
                'has_hook',
                'has_locker',
                'locker_has_lock',
                'has_luggage_space',
                'near_window',
                'near_door',
                'near_radiator',
                'near_air_conditioner',
                'privacy_level',
                'noise_level',
                'is_accessible',
                'suitable_for_tall_person',
                'suitable_for_elderly',
                'suitable_for_limited_mobility',
                'max_guests',
                'min_guest_age',
                'max_guest_age',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'holiday_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'instant_booking_enabled',
                'requires_host_approval',
                'created_at',
                'updated_at',
            ])
            ->where('room_id', $this->roomId)
            ->whereHas('property', fn ($query) => $query
                ->where('host_user_id', auth()->id())
                ->orWhere('user_id', auth()->id()));
    }

    private function hasLocaleTitle(SleepingPlace $place, string $locale): bool
    {
        return filled($place->translations->firstWhere('locale', $locale)?->title);
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('host.sleeping_places.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
