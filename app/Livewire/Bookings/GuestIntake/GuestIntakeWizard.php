<?php

namespace App\Livewire\Bookings\GuestIntake;

use App\Models\BookingGuestIntake;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\BookingGuestIntake\BookingGuestIntakeMessageService;
use App\Services\BookingGuestIntake\BookingGuestIntakeService;
use App\Services\BookingGuestIntake\BookingGuestIntakeSummaryService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestIntakeWizard extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    #[Locked]
    public ?int $intakeId = null;

    public int $step = 1;

    public string $tripPurpose = '';

    public string $tripPurposeOther = '';

    public string $tripPurposeVisibility = 'safe';

    public string $plannedArrivalDate = '';

    public string $plannedArrivalTime = '';

    public string $plannedArrivalWindow = '';

    public string $plannedDepartureTime = '';

    public bool $arrivalTimeUnknown = false;

    public bool $departureTimeUnknown = false;

    public bool $earlyCheckInRequested = false;

    public string $requestedEarlyCheckInTime = '';

    public bool $lateCheckInRequested = false;

    public string $requestedLateCheckInTime = '';

    public bool $lateCheckOutRequested = false;

    public string $requestedLateCheckOutTime = '';

    public bool $earlyCheckOutRequested = false;

    public string $requestedEarlyCheckOutTime = '';

    public bool $canAdjustArrivalTime = true;

    public string $baggageLevel = '';

    public int|string|null $baggageCount = null;

    public bool $hasLargeSuitcase = false;

    public bool $hasSpecialBaggage = false;

    public string $specialBaggageType = '';

    public bool $needsLuggageStorageBeforeCheckin = false;

    public bool $needsLuggageStorageAfterCheckout = false;

    public bool $hasPet = false;

    public string $petType = '';

    public string $petSize = '';

    public string $petNotes = '';

    public ?bool $smokes = null;

    public string $smokingType = '';

    public bool $acceptsSmokingRules = false;

    public bool $needsQuiet = false;

    public string $noiseSensitivityLevel = '';

    public bool $needsWorkspace = false;

    public bool $needsFastWifi = false;

    public bool $needsPowerSocket = false;

    public bool $needsOnlineCalls = false;

    public bool $needsLateEntry = false;

    public bool $needsSelfCheckIn = false;

    public bool $needsRegistration = false;

    public bool $needsWorkDocuments = false;

    public bool $needsInvoice = false;

    public bool $needsReceipt = false;

    public bool $needsContract = false;

    public string $companyName = '';

    public string $documentNotes = '';

    public string $specialRequests = '';

    public string $hostMessage = '';

    public bool $rulesAccepted = false;

    public ?string $statusMessage = null;

    public function mount(int $sleepingPlaceId, BookingGuestIntakeService $service): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;

        $user = auth()->user();
        $place = SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id'])
            ->with([
                'property:id,host_user_id,amenities,rules,noise_level',
                'room:id,property_id,has_desk,has_chair,noise_level,can_work_at_night,can_turn_light_at_night',
            ])
            ->find($sleepingPlaceId);

        if (! $user instanceof User || ! $place instanceof SleepingPlace) {
            return;
        }

        $intake = $service->createDraft($user, $place, []);
        $this->hydrateFromIntake($intake);
    }

    public function saveCurrentStep(BookingGuestIntakeService $service): void
    {
        $intake = $this->intake();
        $user = auth()->user();

        if (! $user instanceof User || ! $intake instanceof BookingGuestIntake) {
            return;
        }

        $updated = $service->updateDraft($user, $intake, $this->payload());
        $this->hydrateFromIntake($updated);
        $this->statusMessage = __('guest_intake.messages.draft_saved');
    }

    public function nextStep(BookingGuestIntakeService $service): void
    {
        $this->saveCurrentStep($service);
        $this->step = min(6, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function complete(BookingGuestIntakeService $service): void
    {
        $this->saveCurrentStep($service);

        $user = auth()->user();
        $intake = $this->intake();

        if (! $user instanceof User || ! $intake instanceof BookingGuestIntake) {
            return;
        }

        $completed = $service->complete($user, $intake);
        $this->hydrateFromIntake($completed);
        $this->statusMessage = __('guest_intake.messages.completed');
    }

    public function generateHostMessage(BookingGuestIntakeMessageService $messages): void
    {
        $intake = $this->intake();

        if (! $intake instanceof BookingGuestIntake) {
            return;
        }

        $this->hostMessage = $messages->generateHostMessage($intake->fill($this->payload()), app()->getLocale());
    }

    public function render(BookingGuestIntakeSummaryService $summaryService, BookingGuestIntakeMessageService $messages): View
    {
        $intake = $this->intake();
        $summary = $intake instanceof BookingGuestIntake ? $summaryService->buildGuestReviewSummary($intake) : null;
        $templates = $intake instanceof BookingGuestIntake ? $messages->suggestMessageTemplates($intake, app()->getLocale()) : [];

        return view('livewire.bookings.guest-intake.guest-intake-wizard', [
            'summary' => $summary,
            'templates' => $templates,
        ]);
    }

    private function intake(): ?BookingGuestIntake
    {
        if (! $this->intakeId) {
            return null;
        }

        return BookingGuestIntake::query()
            ->with(['property:id,amenities,rules,noise_level', 'room:id,has_desk,has_chair,noise_level,can_work_at_night,can_turn_light_at_night', 'sleepingPlace:id,early_check_in_allowed,late_check_out_allowed,has_luggage_space,has_power_socket'])
            ->find($this->intakeId);
    }

    private function hydrateFromIntake(BookingGuestIntake $intake): void
    {
        $this->intakeId = $intake->id;
        $this->tripPurpose = (string) $intake->trip_purpose;
        $this->tripPurposeOther = (string) $intake->trip_purpose_other;
        $this->tripPurposeVisibility = $intake->trip_purpose_visibility ?: 'safe';
        $this->plannedArrivalDate = $intake->planned_arrival_date?->toDateString() ?: '';
        $this->plannedArrivalTime = (string) $intake->planned_arrival_time;
        $this->plannedArrivalWindow = (string) $intake->planned_arrival_window;
        $this->plannedDepartureTime = (string) $intake->planned_departure_time;
        $this->arrivalTimeUnknown = (bool) $intake->arrival_time_unknown;
        $this->departureTimeUnknown = (bool) $intake->departure_time_unknown;
        $this->earlyCheckInRequested = (bool) $intake->early_check_in_requested;
        $this->requestedEarlyCheckInTime = (string) $intake->requested_early_check_in_time;
        $this->lateCheckInRequested = (bool) $intake->late_check_in_requested;
        $this->requestedLateCheckInTime = (string) $intake->requested_late_check_in_time;
        $this->lateCheckOutRequested = (bool) $intake->late_check_out_requested;
        $this->requestedLateCheckOutTime = (string) $intake->requested_late_check_out_time;
        $this->earlyCheckOutRequested = (bool) $intake->early_check_out_requested;
        $this->requestedEarlyCheckOutTime = (string) $intake->requested_early_check_out_time;
        $this->canAdjustArrivalTime = (bool) $intake->can_adjust_arrival_time;
        $this->baggageLevel = (string) $intake->baggage_level;
        $this->baggageCount = $intake->baggage_count;
        $this->hasLargeSuitcase = (bool) $intake->has_large_suitcase;
        $this->hasSpecialBaggage = (bool) $intake->has_special_baggage;
        $this->specialBaggageType = (string) $intake->special_baggage_type;
        $this->needsLuggageStorageBeforeCheckin = (bool) $intake->needs_luggage_storage_before_checkin;
        $this->needsLuggageStorageAfterCheckout = (bool) $intake->needs_luggage_storage_after_checkout;
        $this->hasPet = (bool) $intake->has_pet;
        $this->petType = (string) $intake->pet_type;
        $this->petSize = (string) $intake->pet_size;
        $this->petNotes = (string) $intake->pet_notes;
        $this->smokes = $intake->smokes;
        $this->smokingType = (string) $intake->smoking_type;
        $this->acceptsSmokingRules = (bool) $intake->accepts_smoking_rules;
        $this->needsQuiet = (bool) $intake->needs_quiet;
        $this->noiseSensitivityLevel = (string) $intake->noise_sensitivity_level;
        $this->needsWorkspace = (bool) $intake->needs_workspace;
        $this->needsFastWifi = (bool) $intake->needs_fast_wifi;
        $this->needsPowerSocket = (bool) $intake->needs_power_socket;
        $this->needsOnlineCalls = (bool) $intake->needs_online_calls;
        $this->needsLateEntry = (bool) $intake->needs_late_entry;
        $this->needsSelfCheckIn = (bool) $intake->needs_self_check_in;
        $this->needsRegistration = (bool) $intake->needs_registration;
        $this->needsWorkDocuments = (bool) $intake->needs_work_documents;
        $this->needsInvoice = (bool) $intake->needs_invoice;
        $this->needsReceipt = (bool) $intake->needs_receipt;
        $this->needsContract = (bool) $intake->needs_contract;
        $this->companyName = (string) $intake->company_name;
        $this->documentNotes = (string) $intake->document_notes;
        $this->specialRequests = (string) $intake->special_requests;
        $this->hostMessage = (string) $intake->host_message;
        $this->rulesAccepted = (bool) $intake->rules_accepted;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'trip_purpose' => $this->blankToNull($this->tripPurpose),
            'trip_purpose_other' => $this->blankToNull($this->tripPurposeOther),
            'trip_purpose_visibility' => $this->tripPurposeVisibility ?: 'safe',
            'planned_arrival_date' => $this->blankToNull($this->plannedArrivalDate),
            'planned_arrival_time' => $this->blankToNull($this->plannedArrivalTime),
            'planned_arrival_window' => $this->blankToNull($this->plannedArrivalWindow),
            'planned_departure_time' => $this->blankToNull($this->plannedDepartureTime),
            'arrival_time_unknown' => $this->arrivalTimeUnknown,
            'departure_time_unknown' => $this->departureTimeUnknown,
            'early_check_in_requested' => $this->earlyCheckInRequested,
            'requested_early_check_in_time' => $this->blankToNull($this->requestedEarlyCheckInTime),
            'late_check_in_requested' => $this->lateCheckInRequested,
            'requested_late_check_in_time' => $this->blankToNull($this->requestedLateCheckInTime),
            'late_check_out_requested' => $this->lateCheckOutRequested,
            'requested_late_check_out_time' => $this->blankToNull($this->requestedLateCheckOutTime),
            'early_check_out_requested' => $this->earlyCheckOutRequested,
            'requested_early_check_out_time' => $this->blankToNull($this->requestedEarlyCheckOutTime),
            'can_adjust_arrival_time' => $this->canAdjustArrivalTime,
            'baggage_level' => $this->blankToNull($this->baggageLevel),
            'baggage_count' => $this->baggageCount === '' ? null : $this->baggageCount,
            'has_large_suitcase' => $this->hasLargeSuitcase,
            'has_special_baggage' => $this->hasSpecialBaggage,
            'special_baggage_type' => $this->blankToNull($this->specialBaggageType),
            'needs_luggage_storage_before_checkin' => $this->needsLuggageStorageBeforeCheckin,
            'needs_luggage_storage_after_checkout' => $this->needsLuggageStorageAfterCheckout,
            'has_pet' => $this->hasPet,
            'pet_type' => $this->blankToNull($this->petType),
            'pet_size' => $this->blankToNull($this->petSize),
            'pet_notes' => $this->blankToNull($this->petNotes),
            'smokes' => $this->smokes,
            'smoking_type' => $this->blankToNull($this->smokingType),
            'accepts_smoking_rules' => $this->acceptsSmokingRules,
            'needs_quiet' => $this->needsQuiet,
            'noise_sensitivity_level' => $this->blankToNull($this->noiseSensitivityLevel),
            'needs_workspace' => $this->needsWorkspace,
            'needs_fast_wifi' => $this->needsFastWifi,
            'needs_power_socket' => $this->needsPowerSocket,
            'needs_online_calls' => $this->needsOnlineCalls,
            'needs_late_entry' => $this->needsLateEntry,
            'needs_self_check_in' => $this->needsSelfCheckIn,
            'needs_registration' => $this->needsRegistration,
            'needs_work_documents' => $this->needsWorkDocuments,
            'needs_invoice' => $this->needsInvoice,
            'needs_receipt' => $this->needsReceipt,
            'needs_contract' => $this->needsContract,
            'company_name' => $this->blankToNull($this->companyName),
            'document_notes' => $this->blankToNull($this->documentNotes),
            'special_requests' => $this->blankToNull($this->specialRequests),
            'host_message' => $this->blankToNull($this->hostMessage),
            'rules_accepted' => $this->rulesAccepted,
        ];
    }

    private function blankToNull(string $value): ?string
    {
        return trim($value) === '' ? null : trim($value);
    }
}
