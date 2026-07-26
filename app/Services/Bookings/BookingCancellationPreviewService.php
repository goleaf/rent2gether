<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCancellationPreview;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingCancellationPreviewService
{
    public function __construct(
        private readonly BookingCancellationNumberService $numbers,
        private readonly CancellationPolicySnapshotService $snapshots,
        private readonly BookingCancellationCalculatorService $calculator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPreview(User $user, Booking $booking, array $data): BookingCancellationPreview
    {
        $booking = $this->bookingForCancellation($booking);
        $requestedByType = (string) ($data['requested_by_type'] ?? $this->requestedByType($user, $booking));

        if (! in_array($requestedByType, ['guest', 'host', 'system', 'service_future'], true)) {
            throw ValidationException::withMessages([
                'requested_by_type' => __('cancellations.validation.invalid_requested_by_type'),
            ]);
        }

        $snapshot = $this->snapshots->getForBooking($booking);
        $breakdown = $this->calculator->buildRefundBreakdown($booking, [
            'snapshot' => $snapshot,
            'requested_by_type' => $requestedByType,
            'cancelled_by_type' => $requestedByType,
            'cancellation_type' => $data['cancellation_type'] ?? $snapshot->policy_type,
        ]);

        return BookingCancellationPreview::query()->create([
            'preview_number' => $this->numbers->generatePreviewNumber(),
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'requested_by_user_id' => $user->id,
            'requested_by_type' => $requestedByType,
            'cancellation_type' => (string) ($data['cancellation_type'] ?? $snapshot->policy_type),
            'reason_key' => (string) ($data['reason_key'] ?? 'other'),
            'comment' => $data['comment'] ?? null,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'cancelled_at_preview' => now(),
            ...$this->previewAmounts($breakdown),
            'policy_snapshot_json' => [
                'id' => $snapshot->id,
                'policy_type' => $snapshot->policy_type,
                'title' => $snapshot->title_snapshot,
                'free_cancellation_until' => $snapshot->free_cancellation_until?->toISOString(),
                'rules' => $snapshot->rules_snapshot_json['rules'] ?? [],
            ],
            'refund_breakdown_json' => $breakdown,
            'expires_at' => now()->addMinutes(30),
            'status' => 'calculated',
        ]);
    }

    public function recalculatePreview(BookingCancellationPreview $preview): BookingCancellationPreview
    {
        $preview->loadMissing('booking');

        if (! $preview->booking instanceof Booking) {
            return $preview;
        }

        $snapshot = $this->snapshots->getForBooking($preview->booking);
        $breakdown = $this->calculator->buildRefundBreakdown($preview->booking, [
            'snapshot' => $snapshot,
            'requested_by_type' => $preview->requested_by_type,
            'cancelled_by_type' => $preview->requested_by_type,
            'cancellation_type' => $preview->cancellation_type,
        ]);

        $preview->forceFill([
            ...$this->previewAmounts($breakdown),
            'policy_snapshot_json' => [
                'id' => $snapshot->id,
                'policy_type' => $snapshot->policy_type,
                'title' => $snapshot->title_snapshot,
                'free_cancellation_until' => $snapshot->free_cancellation_until?->toISOString(),
                'rules' => $snapshot->rules_snapshot_json['rules'] ?? [],
            ],
            'refund_breakdown_json' => $breakdown,
            'status' => 'calculated',
        ])->save();

        return $preview->fresh();
    }

    public function expirePreview(BookingCancellationPreview $preview): BookingCancellationPreview
    {
        $preview->forceFill([
            'status' => 'expired',
        ])->save();

        return $preview->fresh();
    }

    public function getForBooking(Booking $booking): ?BookingCancellationPreview
    {
        return BookingCancellationPreview::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'calculated')
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function previewAmounts(array $breakdown): array
    {
        return collect([
            'hours_before_check_in',
            'nights_before_check_in',
            'nights_used',
            'nights_unused',
            'accommodation_amount',
            'cleaning_fee_amount',
            'service_fee_amount',
            'deposit_amount',
            'tax_amount',
            'city_fee_amount',
            'accommodation_refund_amount',
            'cleaning_fee_refund_amount',
            'service_fee_refund_amount',
            'deposit_refund_amount',
            'tax_refund_amount',
            'city_fee_refund_amount',
            'penalty_amount',
            'host_payout_adjustment_amount',
            'total_refund_amount',
            'total_non_refundable_amount',
            'currency',
        ])->mapWithKeys(fn (string $key): array => [$key => $breakdown[$key] ?? null])->all();
    }

    private function requestedByType(User $user, Booking $booking): string
    {
        if ((int) $booking->host_user_id === (int) $user->id) {
            return 'host';
        }

        if ((int) $booking->guest_user_id === (int) $user->id) {
            return 'guest';
        }

        abort(403);
    }

    private function bookingForCancellation(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'nights_count',
                'subtotal',
                'subtotal_amount',
                'accommodation_amount',
                'discount_amount',
                'cleaning_fee',
                'cleaning_fee_amount',
                'deposit',
                'deposit_amount',
                'service_fee',
                'service_fee_amount',
                'tax_amount',
                'city_fee_amount',
                'total',
                'total_amount',
                'total_payable',
                'host_payout_amount',
                'currency',
                'cancellation_policy',
                'free_cancel_before',
                'cancellation_policy_snapshot_id',
                'guest_checked_in_at',
                'checked_in_at',
            ])
            ->with(['sleepingPlace:id,currency,cancellation_policy,user_id,property_id'])
            ->findOrFail($booking->id);
    }
}
