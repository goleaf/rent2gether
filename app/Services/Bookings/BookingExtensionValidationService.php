<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingExtensionValidationResult;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingExtensionValidationService
{
    public function __construct(
        private readonly BookingExtensionAvailabilityService $availability,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array{validation_key:string,severity:string,message_key:string,message_params_json:array<string, mixed>,blocking:bool,visible_to_guest:bool,visible_to_host:bool}>
     */
    public function validateNewCheckoutDate(Booking $booking, array $data): Collection
    {
        $current = $this->date($booking->check_out_date ?? $booking->check_out);
        $new = $this->date($data['new_check_out_date'] ?? $data['requested_new_checkout_date'] ?? $data['new_checkout_date'] ?? $current);

        if ($new->lessThan($current)) {
            return collect([$this->result('new_checkout_before_current_checkout')]);
        }

        if ($new->equalTo($current)) {
            return collect([$this->result('new_checkout_same_as_current_checkout')]);
        }

        return collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateMaxStay(Booking $booking, CarbonInterface $newCheckOut): Collection
    {
        $place = $booking->sleepingPlace;
        $maxNights = (int) ($place?->max_nights ?? 0);

        if ($maxNights <= 0) {
            return collect();
        }

        $checkIn = $this->date($booking->check_in_date ?? $booking->check_in);
        $nights = (int) $checkIn->diffInDays($this->date($newCheckOut));

        if ($nights <= $maxNights) {
            return collect();
        }

        return collect([$this->result('above_max_nights', ['count' => $maxNights])]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateGuestEligibility(Booking $booking): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateGuestCount(Booking $booking): Collection
    {
        $place = $booking->sleepingPlace;
        $maxGuests = (int) ($place?->max_guests_count ?: $place?->max_guests ?: 1);

        if ((int) $booking->guests_count <= $maxGuests) {
            return collect();
        }

        return collect([$this->result('guests_count_too_high', ['count' => $maxGuests])]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateBlocksAndRepairs(Booking $booking, CarbonInterface $newCheckOut, ?BookingExtension $extension = null): Collection
    {
        $availability = $this->availability->checkAvailabilityAfterCurrentCheckout($booking, $this->date($newCheckOut), $extension);

        return $availability['reasons']
            ->map(fn (string $reason): array => $this->result($reason));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function validateDisputesAndComplaints(Booking $booking): Collection
    {
        $results = collect();

        if ((bool) $booking->has_dispute) {
            $results->push($this->result('open_dispute_blocks_extension'));
        }

        if ((bool) $booking->has_complaint) {
            $results->push($this->result('host_confirmation_required', blocking: false, severity: 'warning'));
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createValidationResult(BookingExtension $extension, array $data): BookingExtensionValidationResult
    {
        return BookingExtensionValidationResult::query()->create([
            'booking_extension_id' => $extension->id,
            'validation_key' => $data['validation_key'],
            'severity' => $data['severity'] ?? 'error',
            'message_key' => $data['message_key'] ?? 'booking_extensions.validation.'.$data['validation_key'],
            'message_params_json' => $data['message_params_json'] ?? [],
            'blocking' => $data['blocking'] ?? true,
            'visible_to_guest' => $data['visible_to_guest'] ?? true,
            'visible_to_host' => $data['visible_to_host'] ?? true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{validation_key:string,severity:string,message_key:string,message_params_json:array<string, mixed>,blocking:bool,visible_to_guest:bool,visible_to_host:bool}
     */
    public function result(string $key, array $params = [], bool $blocking = true, string $severity = 'error'): array
    {
        return [
            'validation_key' => $key,
            'severity' => $blocking ? 'blocking' : $severity,
            'message_key' => 'booking_extensions.validation.'.$key,
            'message_params_json' => $params,
            'blocking' => $blocking,
            'visible_to_guest' => true,
            'visible_to_host' => true,
        ];
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }
}
