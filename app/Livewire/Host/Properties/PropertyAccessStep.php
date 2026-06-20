<?php

namespace App\Livewire\Host\Properties;

use App\Models\Property;
use App\Models\User;
use App\Services\Properties\PropertyAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PropertyAccessStep extends Component
{
    public int $propertyId;

    public string $entranceType = '';

    public ?bool $hasIntercom = null;

    public ?bool $hasDoorCode = null;

    public ?bool $hasKey = null;

    public ?bool $hasKeycard = null;

    public ?bool $hasElectronicLock = null;

    public ?bool $hasKeySafe = null;

    public string $keySafeLocationNote = '';

    public ?bool $meetHostRequired = null;

    public ?bool $meetHostRepresentativeRequired = null;

    public ?bool $selfCheckInAvailable = null;

    public ?bool $selfCheckInAvailableAtNight = null;

    public ?bool $access247 = null;

    public ?bool $canReturnAtNight = null;

    public ?bool $hasNightEntryRestrictions = null;

    public string $nightEntryRestrictionText = '';

    public ?bool $guestVisitorsAllowed = null;

    public ?bool $guestVisitorsNeedApproval = null;

    public ?bool $courierRulesEnabled = null;

    public ?bool $deliveryAllowed = null;

    public string $deliveryDropoffLocation = '';

    public bool $wasSaved = false;

    public function mount(Property $property): void
    {
        $this->authorizeHost($property);
        $property->loadMissing('accessDetails');
        $details = $property->accessDetails;

        $this->propertyId = $property->id;

        if (! $details) {
            return;
        }

        $this->entranceType = $details->entrance_type ?: '';
        $this->hasIntercom = $details->has_intercom;
        $this->hasDoorCode = $details->has_door_code;
        $this->hasKey = $details->has_key;
        $this->hasKeycard = $details->has_keycard;
        $this->hasElectronicLock = $details->has_electronic_lock;
        $this->hasKeySafe = $details->has_key_safe;
        $this->keySafeLocationNote = $details->key_safe_location_note ?: '';
        $this->meetHostRequired = $details->meet_host_required;
        $this->meetHostRepresentativeRequired = $details->meet_host_representative_required;
        $this->selfCheckInAvailable = $details->self_check_in_available;
        $this->selfCheckInAvailableAtNight = $details->self_check_in_available_at_night;
        $this->access247 = $details->access_24_7;
        $this->canReturnAtNight = $details->can_return_at_night;
        $this->hasNightEntryRestrictions = $details->has_night_entry_restrictions;
        $this->nightEntryRestrictionText = $details->night_entry_restriction_text ?: '';
        $this->guestVisitorsAllowed = $details->guest_visitors_allowed;
        $this->guestVisitorsNeedApproval = $details->guest_visitors_need_approval;
        $this->courierRulesEnabled = $details->courier_rules_enabled;
        $this->deliveryAllowed = $details->delivery_allowed;
        $this->deliveryDropoffLocation = $details->delivery_dropoff_location ?: '';
    }

    public function save(PropertyAccessService $service): void
    {
        $validated = $this->validate([
            'entranceType' => ['nullable', Rule::in(['shared_entrance', 'private_entrance', 'through_yard', 'through_reception', 'code_door', 'electronic_lock'])],
            'hasIntercom' => ['nullable', 'boolean'],
            'hasDoorCode' => ['nullable', 'boolean'],
            'hasKey' => ['nullable', 'boolean'],
            'hasKeycard' => ['nullable', 'boolean'],
            'hasElectronicLock' => ['nullable', 'boolean'],
            'hasKeySafe' => ['nullable', 'boolean'],
            'keySafeLocationNote' => ['nullable', 'string', 'max:1000'],
            'meetHostRequired' => ['nullable', 'boolean'],
            'meetHostRepresentativeRequired' => ['nullable', 'boolean'],
            'selfCheckInAvailable' => ['nullable', 'boolean'],
            'selfCheckInAvailableAtNight' => ['nullable', 'boolean'],
            'access247' => ['nullable', 'boolean'],
            'canReturnAtNight' => ['nullable', 'boolean'],
            'hasNightEntryRestrictions' => ['nullable', 'boolean'],
            'nightEntryRestrictionText' => ['nullable', 'string', 'max:1000'],
            'guestVisitorsAllowed' => ['nullable', 'boolean'],
            'guestVisitorsNeedApproval' => ['nullable', 'boolean'],
            'courierRulesEnabled' => ['nullable', 'boolean'],
            'deliveryAllowed' => ['nullable', 'boolean'],
            'deliveryDropoffLocation' => ['nullable', 'string', 'max:255'],
        ], attributes: $this->validationAttributes());

        $property = $this->property();
        $this->authorizeHost($property);

        $service->updateAccessDetails($property, [
            'entrance_type' => $this->blankToNull($validated['entranceType']),
            'has_intercom' => $validated['hasIntercom'],
            'has_door_code' => $validated['hasDoorCode'],
            'has_key' => $validated['hasKey'],
            'has_keycard' => $validated['hasKeycard'],
            'has_electronic_lock' => $validated['hasElectronicLock'],
            'has_key_safe' => $validated['hasKeySafe'],
            'key_safe_location_note' => $this->blankToNull($validated['keySafeLocationNote']),
            'key_pickup_contact_type' => $validated['meetHostRepresentativeRequired'] ? 'host_representative' : 'host',
            'meet_host_required' => $validated['meetHostRequired'],
            'meet_host_representative_required' => $validated['meetHostRepresentativeRequired'],
            'self_check_in_available' => $validated['selfCheckInAvailable'],
            'self_check_in_available_at_night' => $validated['selfCheckInAvailableAtNight'],
            'access_24_7' => $validated['access247'],
            'can_return_at_night' => $validated['canReturnAtNight'],
            'has_night_entry_restrictions' => $validated['hasNightEntryRestrictions'],
            'night_entry_restriction_text' => $this->blankToNull($validated['nightEntryRestrictionText']),
            'guest_visitors_allowed' => $validated['guestVisitorsAllowed'],
            'guest_visitors_need_approval' => $validated['guestVisitorsNeedApproval'],
            'courier_rules_enabled' => $validated['courierRulesEnabled'],
            'delivery_allowed' => $validated['deliveryAllowed'],
            'delivery_dropoff_location' => $this->blankToNull($validated['deliveryDropoffLocation']),
        ]);

        $this->wasSaved = true;
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-access-step');
    }

    private function property(): Property
    {
        return Property::query()->findOrFail($this->propertyId);
    }

    private function authorizeHost(Property $property): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $property->isOwnedBy($user), 403);
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = __('property.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
