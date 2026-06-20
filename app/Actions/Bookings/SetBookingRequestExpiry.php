<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetBookingRequestExpiry
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(User $host, Booking $booking, CarbonInterface|string $expiresAt): Booking
    {
        return DB::transaction(function () use ($host, $booking, $expiresAt): Booking {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $this->authorize($host, $booking);
            $this->ensureRequestCanBeChanged($booking);

            $deadline = CarbonImmutable::parse($expiresAt);

            if ($deadline->lessThanOrEqualTo(CarbonImmutable::now())) {
                throw ValidationException::withMessages([
                    'expiryAt' => __('host.requests.errors.expiry_must_be_future'),
                ]);
            }

            $status = $this->statusValue($booking);

            $booking->forceFill([
                'availability_hold_expires_at' => $deadline,
            ])->save();

            $booking->statusHistories()->create([
                'from_status' => $status,
                'to_status' => $status,
                'changed_by_user_id' => $host->id,
                'note' => 'host.requests.history.expiry_updated',
            ]);

            return $booking->refresh();
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function authorize(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('host.requests.errors.not_your_request'));
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureRequestCanBeChanged(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), AcceptBookingRequest::requestStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => __('host.requests.errors.status_changed'),
            ]);
        }
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
