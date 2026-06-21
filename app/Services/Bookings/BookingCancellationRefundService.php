<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCancellationRefundLine;
use App\Models\BookingRefund;
use Illuminate\Support\Collection;

class BookingCancellationRefundService
{
    public function __construct(
        private readonly BookingRefundService $refunds,
    ) {}

    public function createRefundFromCancellation(BookingCancellation $cancellation): ?BookingRefund
    {
        if ((float) $cancellation->total_refund_amount <= 0.0) {
            $cancellation->forceFill(['refund_status' => 'not_required'])->save();

            return null;
        }

        $cancellation->loadMissing('booking');

        if (! $cancellation->booking instanceof Booking) {
            return null;
        }

        $refund = $this->refunds->createRefund($cancellation->booking, (float) $cancellation->total_refund_amount, 'cancellation_refund', [
            'reason_key' => $cancellation->reason_key,
            'source_type' => 'booking_cancellation',
            'source_id' => $cancellation->id,
            'comment' => $cancellation->comment,
        ]);

        $cancellation->forceFill([
            'booking_refund_id' => $refund->id,
            'refund_status' => 'pending',
        ])->save();

        return $refund;
    }

    /**
     * @return Collection<int, BookingCancellationRefundLine>
     */
    public function createRefundLines(BookingCancellation $cancellation): Collection
    {
        $lines = $cancellation->preview?->refund_breakdown_json['lines'] ?? $this->linesFromCancellation($cancellation);
        $created = collect();

        foreach ($lines as $line) {
            $created->push($cancellation->refundLines()->create($line));
        }

        return $created;
    }

    public function markRefundProcessing(BookingCancellation $cancellation): BookingCancellation
    {
        return $this->transitionRefund($cancellation, 'processing');
    }

    public function markRefundCompleted(BookingCancellation $cancellation): BookingCancellation
    {
        return $this->transitionRefund($cancellation, 'completed', ['completed_at' => now()]);
    }

    public function markRefundFailed(BookingCancellation $cancellation, string $reason): BookingCancellation
    {
        return $this->transitionRefund($cancellation, 'failed', ['comment' => trim(($cancellation->comment ? $cancellation->comment."\n" : '').$reason)]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linesFromCancellation(BookingCancellation $cancellation): array
    {
        return [
            $this->line($cancellation, 'accommodation', $cancellation->accommodation_amount, $cancellation->accommodation_refund_amount, 10),
            $this->line($cancellation, 'cleaning_fee', $cancellation->cleaning_fee_amount, $cancellation->cleaning_fee_refund_amount, 20),
            $this->line($cancellation, 'service_fee', $cancellation->service_fee_amount, $cancellation->service_fee_refund_amount, 30),
            $this->line($cancellation, 'deposit', $cancellation->deposit_amount, $cancellation->deposit_refund_amount, 40),
            $this->line($cancellation, 'penalty', $cancellation->penalty_amount, 0, 70, false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function line(BookingCancellation $cancellation, string $type, mixed $amount, mixed $refund, int $sortOrder, bool $refundable = true): array
    {
        return [
            'line_type' => $type,
            'label_key' => 'cancellations.refund_line_types.'.$type,
            'amount' => $amount,
            'currency' => $cancellation->currency,
            'refundable' => $refundable && (float) $refund > 0,
            'refund_amount' => $refund,
            'non_refundable_amount' => max(0, (float) $amount - (float) $refund),
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transitionRefund(BookingCancellation $cancellation, string $status, array $attributes = []): BookingCancellation
    {
        $cancellation->forceFill([
            ...$attributes,
            'refund_status' => $status,
        ])->save();

        return $cancellation->fresh();
    }
}
