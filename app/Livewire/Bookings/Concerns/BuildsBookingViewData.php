<?php

namespace App\Livewire\Bookings\Concerns;

use App\Models\Booking;
use Illuminate\Support\Number;

trait BuildsBookingViewData
{
    protected function loadBooking(int $bookingId): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'booking_number',
                'reference',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'booking_type',
                'approval_type',
                'payment_type',
                'deposit_mode',
                'guest_group_type',
                'status',
                'payment_status',
                'verification_status',
                'check_in_date',
                'check_in_time',
                'check_out_date',
                'check_out_time',
                'nights_count',
                'chargeable_days_count',
                'calendar_presence_days_count',
                'guests_count',
                'total_payable',
                'total_without_deposit',
                'deposit_amount',
                'cleaning_fee_amount',
                'service_fee_amount',
                'host_payout_amount',
                'refundable_amount',
                'non_refundable_amount',
                'currency',
                'payment_deadline_at',
                'guest_message',
                'host_response',
                'has_dispute',
                'has_complaint',
                'closed_at',
            ])
            ->with([
                'guest:id,name,email',
                'host:id,name,email',
                'property:id,title,name',
                'room:id,name,property_id',
                'sleepingPlace:id,room_id,property_id,display_name,title',
                'requirements:id,booking_id,requirement_key,status,required,completed_at,message_key',
                'lifecycleEvents:id,booking_id,event_key,event_type,occurred_at',
                'statusLogs:id,booking_id,old_status,new_status,created_at',
            ])
            ->findOrFail($bookingId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function bookingSummary(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number ?: $booking->reference,
            'status' => $this->statusLabel($booking->status),
            'status_key' => $this->value($booking->status),
            'status_color' => $this->statusColor($this->value($booking->status)),
            'payment_status' => $this->paymentStatusLabel($booking->payment_status),
            'verification_status' => __('bookings.verification_statuses.'.$booking->verification_status),
            'booking_type' => __('bookings.booking_types.'.$this->value($booking->booking_type)),
            'approval_type' => __('bookings.approval_types.'.$booking->approval_type),
            'payment_type' => __('bookings.payment_types.'.$booking->payment_type),
            'deposit_mode' => __('bookings.deposit_modes.'.$booking->deposit_mode),
            'guest_group_type' => __('bookings.guest_group_types.'.$booking->guest_group_type),
            'guest_name' => $booking->guest?->name,
            'host_name' => $booking->host?->name,
            'sleeping_place' => $booking->sleepingPlace?->display_name ?: $booking->sleepingPlace?->title,
            'room' => $booking->room?->name,
            'property' => $booking->property?->title ?: $booking->property?->name,
            'dates' => trim(($booking->check_in_date?->translatedFormat('d M') ?: '').' - '.($booking->check_out_date?->translatedFormat('d M') ?: '')),
            'check_in_date' => $booking->check_in_date?->translatedFormat('d M Y'),
            'check_out_date' => $booking->check_out_date?->translatedFormat('d M Y'),
            'check_in_time' => $booking->check_in_time?->format('H:i'),
            'check_out_time' => $booking->check_out_time?->format('H:i'),
            'nights_count' => (int) $booking->nights_count,
            'chargeable_days_count' => (int) $booking->chargeable_days_count,
            'calendar_presence_days_count' => (int) $booking->calendar_presence_days_count,
            'guests_count' => (int) $booking->guests_count,
            'total_payable' => $this->money($booking->total_payable, $booking->currency),
            'total_without_deposit' => $this->money($booking->total_without_deposit, $booking->currency),
            'deposit_amount' => $this->money($booking->deposit_amount, $booking->currency),
            'cleaning_fee_amount' => $this->money($booking->cleaning_fee_amount, $booking->currency),
            'service_fee_amount' => $this->money($booking->service_fee_amount, $booking->currency),
            'host_payout_amount' => $this->money($booking->host_payout_amount, $booking->currency),
            'refundable_amount' => $this->money($booking->refundable_amount, $booking->currency),
            'non_refundable_amount' => $this->money($booking->non_refundable_amount, $booking->currency),
            'payment_deadline_at' => $booking->payment_deadline_at?->translatedFormat('d M, H:i'),
            'guest_message' => $booking->guest_message,
            'host_response' => $booking->host_response,
            'has_dispute' => (bool) $booking->has_dispute,
            'has_complaint' => (bool) $booking->has_complaint,
        ];
    }

    protected function money(mixed $amount, ?string $currency): string
    {
        return Number::currency((float) $amount, $currency ?: 'EUR', app()->getLocale());
    }

    protected function statusLabel(mixed $status): string
    {
        return __('bookings.statuses.'.$this->value($status));
    }

    protected function paymentStatusLabel(mixed $status): string
    {
        return __('bookings.payment_statuses.'.$this->value($status));
    }

    protected function value(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }

    protected function statusColor(string $status): string
    {
        return match ($status) {
            'confirmed', 'paid', 'ready_for_check_in', 'guest_checked_in', 'stay_in_progress', 'completed' => 'lime',
            'waiting_payment', 'waiting_host_confirmation', 'waiting_guest_response', 'waiting_identity_verification', 'waiting_document_verification' => 'amber',
            'rejected_by_host', 'cancelled_by_guest', 'cancelled_by_host', 'payment_failed', 'no_show', 'host_unresponsive', 'dispute_opened' => 'red',
            default => 'zinc',
        };
    }
}
