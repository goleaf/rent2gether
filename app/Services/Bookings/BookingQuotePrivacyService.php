<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\User;

class BookingQuotePrivacyService
{
    /**
     * Determines whether a user can see this quote without exposing private host internals.
     */
    public function canView(User $user, BookingQuote $quote): bool
    {
        return (int) $quote->user_id === (int) $user->id
            || ((int) $quote->host_user_id === (int) $user->id
                && in_array($quote->status, [
                    BookingQuote::STATUS_CONVERTED_TO_BOOKING,
                    BookingQuote::STATUS_CONVERTED_TO_REQUEST,
                ], true));
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingQuote $quote): array
    {
        abort_unless((int) $quote->user_id === (int) $guest->id, 403);

        return $this->basePayload($quote);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingQuote $quote): array
    {
        abort_unless((int) $quote->host_user_id === (int) $host->id, 403);

        return [
            ...$this->basePayload($quote),
            'host_payout_preview_amount' => (float) $quote->host_payout_preview_amount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(BookingQuote $quote): array
    {
        $quote->loadMissing(['lines', 'validationResults', 'timelineDates', 'suggestions']);

        return [
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'sleeping_place_id' => $quote->sleeping_place_id,
            'room_id' => $quote->room_id,
            'property_id' => $quote->property_id,
            'check_in_date' => $quote->check_in_date?->toDateString(),
            'check_out_date' => $quote->check_out_date?->toDateString(),
            'check_in_time' => $quote->check_in_time,
            'check_out_time' => $quote->check_out_time,
            'nights_count' => (int) $quote->nights_count,
            'chargeable_days_count' => (int) $quote->chargeable_days_count,
            'calendar_presence_days_count' => (int) $quote->calendar_presence_days_count,
            'guests_count' => (int) $quote->guests_count,
            'availability_status' => $quote->availability_status,
            'validation_status' => $quote->validation_status,
            'pricing_status' => $quote->pricing_status,
            'accommodation_amount' => (float) $quote->accommodation_amount,
            'discount_amount' => (float) $quote->discount_amount,
            'cleaning_fee_amount' => (float) $quote->cleaning_fee_amount,
            'service_fee_amount' => (float) $quote->service_fee_amount,
            'deposit_amount' => (float) $quote->deposit_amount,
            'total_without_deposit' => (float) $quote->total_without_deposit,
            'total_payable' => (float) $quote->total_payable,
            'refundable_amount' => (float) $quote->refundable_amount,
            'non_refundable_amount' => (float) $quote->non_refundable_amount,
            'currency' => $quote->currency,
            'free_cancellation_until' => $quote->free_cancellation_until?->toIso8601String(),
            'cancellation_penalty_starts_at' => $quote->cancellation_penalty_starts_at?->toIso8601String(),
            'payment_deadline_at' => $quote->payment_deadline_at?->toIso8601String(),
            'expires_at' => $quote->expires_at?->toIso8601String(),
            'status' => $quote->status,
            'lines' => $quote->lines->map(fn ($line): array => [
                'type' => $line->line_type,
                'label_key' => $line->label_key,
                'date' => $line->date?->toDateString(),
                'amount' => (float) $line->amount,
                'currency' => $line->currency,
                'is_refundable' => (bool) $line->is_refundable,
                'is_deposit' => (bool) $line->is_deposit,
            ])->values()->all(),
            'validation_results' => $quote->validationResults
                ->where('visible_to_guest', true)
                ->map(fn ($result): array => [
                    'validation_key' => $result->validation_key,
                    'severity' => $result->severity,
                    'message_key' => $result->message_key,
                    'blocking' => (bool) $result->blocking,
                ])
                ->values()
                ->all(),
            'timeline_dates' => $quote->timelineDates->map(fn ($date): array => [
                'event_key' => $date->event_key,
                'scheduled_at' => $date->scheduled_at?->toIso8601String(),
                'status' => $date->status,
            ])->values()->all(),
            'suggestions' => $quote->suggestions->map(fn ($suggestion): array => [
                'suggestion_type' => $suggestion->suggestion_type,
                'sleeping_place_id' => $suggestion->sleeping_place_id,
                'room_id' => $suggestion->room_id,
                'property_id' => $suggestion->property_id,
                'check_in_date' => $suggestion->check_in_date?->toDateString(),
                'check_out_date' => $suggestion->check_out_date?->toDateString(),
                'nights_count' => $suggestion->nights_count,
                'price_preview_amount' => $suggestion->price_preview_amount === null ? null : (float) $suggestion->price_preview_amount,
                'currency' => $suggestion->currency,
                'message_key' => $suggestion->message_key,
            ])->values()->all(),
        ];
    }
}
