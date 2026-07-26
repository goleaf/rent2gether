<?php

namespace App\Livewire\Host\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Models\Booking;
use App\Services\CheckIn\BookingCheckInService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostCheckInDetailsSheet extends Component
{
    use LoadsBookingCheckIn {
        mount as mountCheckIn;
    }

    public string $actualArrivalTime = '';

    public string $metByName = '';

    public bool $keysHandedOver = false;

    public bool $doorCodeShared = false;

    public bool $roomShown = false;

    public bool $sleepingPlaceShown = false;

    public bool $rulesExplained = false;

    public bool $beddingGiven = false;

    public bool $towelGiven = false;

    public bool $lockerGiven = false;

    public function mount(Booking|int|null $booking = null, ?int $checkInId = null): void
    {
        $this->mountCheckIn($booking, $checkInId);
        $this->fillChecklistForm();
    }

    public function saveChecklist(): void
    {
        $checkIn = $this->checkIn();
        $user = Auth::user();

        if (! $checkIn || ! $user) {
            return;
        }

        $validated = $this->validate([
            'actualArrivalTime' => ['nullable', 'date_format:H:i'],
            'metByName' => ['nullable', 'string', 'max:120'],
            'keysHandedOver' => ['boolean'],
            'doorCodeShared' => ['boolean'],
            'roomShown' => ['boolean'],
            'sleepingPlaceShown' => ['boolean'],
            'rulesExplained' => ['boolean'],
            'beddingGiven' => ['boolean'],
            'towelGiven' => ['boolean'],
            'lockerGiven' => ['boolean'],
        ], [], $this->validationAttributes());

        app(BookingCheckInService::class)->recordHostChecklist($user, $checkIn, [
            'actual_arrival_at' => $this->arrivalDateTime($checkIn, $validated['actualArrivalTime'] ?? null),
            'met_by_type' => 'host',
            'met_by_name' => $validated['metByName'] ?? null,
            'keys_handed_over' => (bool) $validated['keysHandedOver'],
            'door_code_shared' => (bool) $validated['doorCodeShared'],
            'room_shown' => (bool) $validated['roomShown'],
            'sleeping_place_shown' => (bool) $validated['sleepingPlaceShown'],
            'rules_explained' => (bool) $validated['rulesExplained'],
            'bedding_given' => (bool) $validated['beddingGiven'],
            'towel_given' => (bool) $validated['towelGiven'],
            'locker_given' => (bool) $validated['lockerGiven'],
        ]);

        $this->refreshCheckInState();
        $this->fillChecklistForm();
    }

    public function confirm(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInService::class)->confirmByHost(Auth::user(), $checkIn);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.host.check-in.details-sheet', $this->checkInViewData('host_details_sheet'));
    }

    private function fillChecklistForm(): void
    {
        $checkIn = $this->checkIn();

        if (! $checkIn) {
            return;
        }

        $this->actualArrivalTime = $checkIn->actual_arrival_at?->format('H:i') ?? '';
        $this->metByName = $checkIn->met_by_name ?? '';
        $this->keysHandedOver = (bool) $checkIn->keys_handed_over;
        $this->doorCodeShared = (bool) ($checkIn->door_code_shared || $checkIn->door_code_provided);
        $this->roomShown = (bool) $checkIn->room_shown;
        $this->sleepingPlaceShown = (bool) $checkIn->sleeping_place_shown;
        $this->rulesExplained = (bool) $checkIn->rules_explained;
        $this->beddingGiven = (bool) ($checkIn->bedding_given || $checkIn->bedding_issued);
        $this->towelGiven = (bool) ($checkIn->towel_given || $checkIn->towel_issued);
        $this->lockerGiven = (bool) ($checkIn->locker_given || $checkIn->locker_assigned);
    }

    private function arrivalDateTime(mixed $checkIn, ?string $time): ?CarbonImmutable
    {
        $time = trim((string) $time);

        if ($time === '') {
            return null;
        }

        return CarbonImmutable::parse($checkIn->check_in_date->format('Y-m-d').' '.$time);
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'actualArrivalTime' => __('check_in.validation.attributes.actual_arrival_time'),
            'metByName' => __('check_in.validation.attributes.met_by'),
            'keysHandedOver' => __('check_in.fields.keys_handed_over'),
            'doorCodeShared' => __('check_in.fields.door_code_shared'),
            'roomShown' => __('check_in.fields.room_shown'),
            'sleepingPlaceShown' => __('check_in.fields.sleeping_place_shown'),
            'rulesExplained' => __('check_in.fields.rules_explained'),
            'beddingGiven' => __('check_in.fields.bedding_given'),
            'towelGiven' => __('check_in.fields.towel_given'),
            'lockerGiven' => __('check_in.fields.locker_given'),
        ];
    }
}
