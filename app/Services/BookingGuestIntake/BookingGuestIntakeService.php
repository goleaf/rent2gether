<?php

namespace App\Services\BookingGuestIntake;

use App\Models\Booking;
use App\Models\BookingGuestIntake;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingGuestIntakeService
{
    /**
     * @var list<string>
     */
    private const FIELDS = [
        'trip_purpose',
        'trip_purpose_other',
        'trip_purpose_visibility',
        'planned_arrival_date',
        'planned_arrival_time',
        'planned_arrival_window',
        'planned_departure_time',
        'needs_early_check_in',
        'needs_late_check_out',
        'luggage_amount',
        'arrival_time_unknown',
        'departure_time_unknown',
        'early_check_in_requested',
        'requested_early_check_in_time',
        'late_check_in_requested',
        'requested_late_check_in_time',
        'late_check_out_requested',
        'requested_late_check_out_time',
        'early_check_out_requested',
        'requested_early_check_out_time',
        'can_adjust_arrival_time',
        'baggage_level',
        'baggage_count',
        'has_large_suitcase',
        'has_special_baggage',
        'special_baggage_type',
        'needs_luggage_storage_before_checkin',
        'needs_luggage_storage_after_checkout',
        'has_pet',
        'pet_type',
        'pet_size',
        'pet_notes',
        'smokes',
        'smoking_type',
        'accepts_smoking_rules',
        'needs_quiet',
        'needs_desk',
        'noise_sensitivity_level',
        'needs_workspace',
        'needs_fast_wifi',
        'needs_power_socket',
        'needs_online_calls',
        'needs_late_entry',
        'needs_self_check_in',
        'needs_registration',
        'needs_work_documents',
        'needs_invoice',
        'needs_receipt',
        'needs_contract',
        'company_name',
        'document_notes',
        'special_requests',
        'message_to_host',
        'host_message',
        'rules_accepted',
    ];

    /**
     * @var list<string>
     */
    private const TRIP_PURPOSES = [
        'tourism',
        'work',
        'study',
        'relocation',
        'business_trip',
        'medical',
        'housing_search',
        'other',
    ];

    /**
     * @var list<string>
     */
    private const BAGGAGE_LEVELS = [
        'none',
        'small_bag',
        'one_bag',
        'two_bags',
        'many_bags',
    ];

    /**
     * @var list<string>
     */
    private const PET_TYPES = ['cat', 'dog', 'other'];

    /**
     * @var list<string>
     */
    private const PET_SIZES = ['small', 'medium', 'large'];

    /**
     * @var list<string>
     */
    private const NOISE_LEVELS = ['low', 'medium', 'high'];

    public function __construct(
        private readonly BookingGuestIntakeCompatibilityService $compatibility,
        private readonly BookingGuestIntakeMessageService $messageService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForBooking(User $guest, array $data): BookingGuestIntake
    {
        $place = SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id'])
            ->findOrFail((int) $data['sleeping_place_id']);

        return BookingGuestIntake::query()->create($this->bookingPayload($guest, $place, $data));
    }

    /**
     * @param  object|array<string, mixed>  $quote
     * @param  array<string, mixed>  $data
     */
    public function createForQuote(User $guest, object|array $quote, array $data): BookingGuestIntake
    {
        $quoteData = is_array($quote) ? $quote : [
            'id' => $quote->id ?? null,
            'sleeping_place_id' => $quote->sleeping_place_id ?? null,
        ];

        return $this->createForBooking($guest, [
            ...$data,
            'booking_quote_id' => $quoteData['id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? $quoteData['sleeping_place_id'],
        ]);
    }

    /**
     * @param  object|array<string, mixed>  $request
     * @param  array<string, mixed>  $data
     */
    public function createForRequest(User $guest, object|array $request, array $data): BookingGuestIntake
    {
        $requestData = is_array($request) ? $request : [
            'id' => $request->id ?? null,
            'sleeping_place_id' => $request->sleeping_place_id ?? null,
        ];

        return $this->createForBooking($guest, [
            ...$data,
            'booking_request_id' => $requestData['id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? $requestData['sleeping_place_id'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createDraft(User $user, SleepingPlace $place, array $context): BookingGuestIntake
    {
        $place->loadMissing(['property:id,host_user_id,amenities,rules,noise_level', 'room:id,property_id,has_desk,has_chair,noise_level,can_work_at_night,can_turn_light_at_night']);

        return BookingGuestIntake::query()->firstOrCreate([
            'user_id' => $user->id,
            'sleeping_place_id' => $place->id,
            'status' => 'draft',
            'booking_id' => null,
        ], [
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'trip_purpose_visibility' => 'safe',
            'planned_arrival_date' => Arr::get($context, 'check_in'),
            'planned_arrival_time' => Arr::get($context, 'planned_arrival_time'),
            'planned_departure_time' => Arr::get($context, 'planned_departure_time'),
            'can_adjust_arrival_time' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(User $user, BookingGuestIntake $intake, array $data): BookingGuestIntake
    {
        $this->authorizeOwner($user, $intake);

        $payload = $this->validatedPayload($data);

        if (array_key_exists('message_to_host', $payload) && filled($payload['message_to_host'])) {
            $payload['message_to_host'] = $this->messageService->cleanHostMessage((string) $payload['message_to_host']);
        }

        if (array_key_exists('host_message', $payload) && filled($payload['host_message'])) {
            $payload['host_message'] = $this->messageService->cleanHostMessage((string) $payload['host_message']);
        }

        if (($payload['rules_accepted'] ?? false) && ! $intake->rules_accepted_at) {
            $payload['rules_accepted_at'] = now();
        }

        $intake->fill($payload)->save();

        return $intake->refresh();
    }

    public function complete(User $user, BookingGuestIntake $intake): BookingGuestIntake
    {
        $this->authorizeOwner($user, $intake);

        $validator = Validator::make($this->normalizeScalars($intake->getAttributes()), [
            'trip_purpose' => ['required', 'string', Rule::in(self::TRIP_PURPOSES)],
            'trip_purpose_other' => ['nullable', 'required_if:trip_purpose,other', 'string', 'max:500'],
            'rules_accepted' => ['accepted'],
        ], attributes: $this->validationAttributes());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->compatibility->checkAgainstPlace($intake);
        $intake->forceFill([
            'status' => 'completed',
            'rules_accepted_at' => $intake->rules_accepted_at ?: now(),
            'compatibility_checked_at' => now(),
            'compatibility_status' => $result['status'],
            'compatibility_score' => $result['score'],
            'warnings_json' => $result['warnings'],
            'blocking_reasons_json' => $result['blocking_reasons'],
            'auto_generated_host_message' => $this->messageService->generateHostMessage($intake, app()->getLocale()),
        ])->save();

        return $intake->refresh();
    }

    public function attachToBooking(BookingGuestIntake $intake, Booking $booking): BookingGuestIntake
    {
        $intake->forceFill([
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'user_id' => $booking->guest_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => 'completed',
        ])->save();

        return $intake->refresh();
    }

    public function copyToBooking(BookingGuestIntake $intake, Booking $booking): BookingGuestIntake
    {
        return $this->attachToBooking($intake, $booking);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildHostSummary(BookingGuestIntake $intake): array
    {
        return [
            'trip_purpose' => $intake->trip_purpose,
            'planned_arrival_time' => $intake->planned_arrival_time,
            'planned_departure_time' => $intake->planned_departure_time,
            'needs_early_check_in' => (bool) ($intake->needs_early_check_in || $intake->early_check_in_requested),
            'needs_late_check_out' => (bool) ($intake->needs_late_check_out || $intake->late_check_out_requested),
            'luggage_amount' => $intake->luggage_amount ?: $intake->baggage_level,
            'has_large_suitcase' => (bool) $intake->has_large_suitcase,
            'has_pet' => (bool) $intake->has_pet,
            'smokes' => (bool) $intake->smokes,
            'needs_quiet' => (bool) $intake->needs_quiet,
            'needs_desk' => (bool) ($intake->needs_desk || $intake->needs_workspace),
            'needs_fast_wifi' => (bool) $intake->needs_fast_wifi,
            'needs_registration' => (bool) $intake->needs_registration,
            'needs_work_documents' => (bool) $intake->needs_work_documents,
            'special_requests' => $intake->special_requests,
            'message_to_host' => $intake->message_to_host ?: $intake->host_message,
        ];
    }

    public function deleteDraft(User $user, BookingGuestIntake $intake): void
    {
        $this->authorizeOwner($user, $intake);

        if ($intake->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => __('guest_intake.validation.only_drafts_can_be_deleted'),
            ]);
        }

        $intake->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validatedPayload(array $data): array
    {
        $payload = $this->normalizeScalars(Arr::only($data, self::FIELDS));

        $validated = Validator::make($payload, [
            'trip_purpose' => ['nullable', 'string', 'max:50', Rule::in(self::TRIP_PURPOSES)],
            'trip_purpose_other' => ['nullable', 'string', 'max:500'],
            'trip_purpose_visibility' => ['nullable', 'in:safe,exact'],
            'planned_arrival_date' => ['nullable', 'date'],
            'planned_arrival_time' => ['nullable', 'date_format:H:i'],
            'planned_arrival_window' => ['nullable', 'string', 'max:100'],
            'planned_departure_time' => ['nullable', 'date_format:H:i'],
            'needs_early_check_in' => ['nullable', 'boolean'],
            'needs_late_check_out' => ['nullable', 'boolean'],
            'luggage_amount' => ['nullable', 'string', Rule::in(self::BAGGAGE_LEVELS)],
            'arrival_time_unknown' => ['nullable', 'boolean'],
            'departure_time_unknown' => ['nullable', 'boolean'],
            'early_check_in_requested' => ['nullable', 'boolean'],
            'requested_early_check_in_time' => ['nullable', 'date_format:H:i'],
            'late_check_in_requested' => ['nullable', 'boolean'],
            'requested_late_check_in_time' => ['nullable', 'date_format:H:i'],
            'late_check_out_requested' => ['nullable', 'boolean'],
            'requested_late_check_out_time' => ['nullable', 'date_format:H:i'],
            'early_check_out_requested' => ['nullable', 'boolean'],
            'requested_early_check_out_time' => ['nullable', 'date_format:H:i'],
            'can_adjust_arrival_time' => ['nullable', 'boolean'],
            'baggage_level' => ['nullable', 'string', Rule::in(self::BAGGAGE_LEVELS)],
            'baggage_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'has_large_suitcase' => ['nullable', 'boolean'],
            'has_special_baggage' => ['nullable', 'boolean'],
            'special_baggage_type' => ['nullable', 'string', 'max:100'],
            'needs_luggage_storage_before_checkin' => ['nullable', 'boolean'],
            'needs_luggage_storage_after_checkout' => ['nullable', 'boolean'],
            'has_pet' => ['nullable', 'boolean'],
            'pet_type' => ['nullable', 'string', Rule::in(self::PET_TYPES)],
            'pet_size' => ['nullable', 'string', Rule::in(self::PET_SIZES)],
            'pet_notes' => ['nullable', 'string', 'max:500'],
            'smokes' => ['nullable', 'boolean'],
            'smoking_type' => ['nullable', 'string', 'max:50'],
            'accepts_smoking_rules' => ['nullable', 'boolean'],
            'needs_quiet' => ['nullable', 'boolean'],
            'needs_desk' => ['nullable', 'boolean'],
            'noise_sensitivity_level' => ['nullable', 'string', Rule::in(self::NOISE_LEVELS)],
            'needs_workspace' => ['nullable', 'boolean'],
            'needs_fast_wifi' => ['nullable', 'boolean'],
            'needs_power_socket' => ['nullable', 'boolean'],
            'needs_online_calls' => ['nullable', 'boolean'],
            'needs_late_entry' => ['nullable', 'boolean'],
            'needs_self_check_in' => ['nullable', 'boolean'],
            'needs_registration' => ['nullable', 'boolean'],
            'needs_work_documents' => ['nullable', 'boolean'],
            'needs_invoice' => ['nullable', 'boolean'],
            'needs_receipt' => ['nullable', 'boolean'],
            'needs_contract' => ['nullable', 'boolean'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'document_notes' => ['nullable', 'string', 'max:500'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'message_to_host' => ['nullable', 'string', 'max:1000'],
            'host_message' => ['nullable', 'string', 'max:1000'],
            'rules_accepted' => ['nullable', 'boolean'],
        ], attributes: $this->validationAttributes())->validate();

        return $this->syncAliases($validated);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function bookingPayload(User $guest, SleepingPlace $place, array $data): array
    {
        $validated = $this->validatedPayload($data);
        $allowed = Arr::only($data, [
            'booking_quote_id',
            'booking_request_id',
            'booking_id',
        ]);

        return [
            ...$allowed,
            ...$validated,
            'user_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'status' => 'draft',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function syncAliases(array $payload): array
    {
        $payload = $this->syncBooleanAlias($payload, 'needs_early_check_in', 'early_check_in_requested');
        $payload = $this->syncBooleanAlias($payload, 'needs_late_check_out', 'late_check_out_requested');
        $payload = $this->syncStringAlias($payload, 'luggage_amount', 'baggage_level');
        $payload = $this->syncBooleanAlias($payload, 'needs_desk', 'needs_workspace');
        $payload = $this->syncStringAlias($payload, 'message_to_host', 'host_message');

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function syncBooleanAlias(array $payload, string $publicField, string $legacyField): array
    {
        if (! array_key_exists($publicField, $payload) && ! array_key_exists($legacyField, $payload)) {
            return $payload;
        }

        $value = array_key_exists($publicField, $payload)
            ? $payload[$publicField]
            : $payload[$legacyField];

        $payload[$publicField] = (bool) $value;
        $payload[$legacyField] = (bool) $value;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function syncStringAlias(array $payload, string $publicField, string $legacyField): array
    {
        if (! array_key_exists($publicField, $payload) && ! array_key_exists($legacyField, $payload)) {
            return $payload;
        }

        $value = array_key_exists($publicField, $payload)
            ? $payload[$publicField]
            : $payload[$legacyField];

        $payload[$publicField] = $value;
        $payload[$legacyField] = $value;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeScalars(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value) === '' ? null : trim($value);
            }
        }

        return $payload;
    }

    private function authorizeOwner(User $user, BookingGuestIntake $intake): void
    {
        if ((int) $intake->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = __('guest_intake.validation.attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
