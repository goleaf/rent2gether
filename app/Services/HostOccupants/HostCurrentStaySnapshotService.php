<?php

namespace App\Services\HostOccupants;

use App\Enums\BookingExtensionStatus;
use App\Enums\BookingStatus;
use App\Enums\ComplaintStatus;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\HostCleaningTask;
use App\Models\HostCurrentStaySnapshot;
use App\Models\HostGuestStayNote;
use App\Models\Property;
use App\Models\User;
use Carbon\CarbonImmutable;

class HostCurrentStaySnapshotService
{
    public function refreshForBooking(Booking $booking): HostCurrentStaySnapshot
    {
        $booking->loadMissing([
            'guest:id,name,email,phone,phone_verified,avatar,rating_as_guest',
            'room:id,title',
            'sleepingPlace:id,display_name,place_number',
            'paymentRecords:id,booking_id,amount,status',
            'complaints:id,booking_id,status',
            'extensions:id,booking_id,status,created_at',
            'payout:id,booking_id,status',
        ]);

        $total = (float) ($booking->total_amount ?? $booking->total ?? 0);
        $paid = $this->paidAmount($booking, $total);
        $remaining = max(0, $total - $paid);
        $openComplaints = $this->openComplaintsCount($booking);
        $latestExtension = $this->latestOpenExtension($booking);
        $lastNote = HostGuestStayNote::query()
            ->where('booking_id', $booking->id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('id')
            ->first(['note', 'updated_at']);
        $checkoutDueToday = $this->checkoutDueToday($booking);
        $checkoutOverdue = $this->checkoutOverdue($booking);
        $needsCleaning = $this->needsCleaningAfterCheckout($booking) || $checkoutDueToday || $checkoutOverdue;

        return HostCurrentStaySnapshot::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->host_user_id,
                'guest_user_id' => $booking->guest_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'guest_display_name' => $booking->guest?->name,
                'guest_avatar_url' => $booking->guest?->avatar,
                'room_label' => $booking->room?->title,
                'sleeping_place_label' => $booking->sleepingPlace?->display_name ?: $booking->sleepingPlace?->place_number,
                'check_in_date' => $this->dateString($booking->check_in_date),
                'check_in_time' => $booking->check_in_time?->format('H:i'),
                'check_out_date' => $this->dateString($booking->check_out_date),
                'check_out_time' => $booking->check_out_time?->format('H:i'),
                'nights_count' => $booking->nights_count ?: $this->nightsCount($booking),
                'nights_left' => $this->calculateNightsLeft($booking),
                'payment_status' => $this->detectPaymentStatus($booking),
                'stay_status' => $this->detectStayStatus($booking),
                'check_in_status' => $this->detectCheckInStatus($booking),
                'payout_status' => $this->value($booking->payout?->status),
                'booking_total_amount' => $total,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
                'deposit_amount' => $booking->deposit_amount,
                'cleaning_fee_amount' => $booking->cleaning_fee_amount,
                'has_special_requests' => filled($booking->guest_message),
                'special_requests_summary' => $booking->guest_message,
                'guest_rating_average' => $booking->guest?->rating_as_guest,
                'roommate_rating_average' => null,
                'has_complaints' => $openComplaints > 0,
                'open_complaints_count' => $openComplaints,
                'needs_extension' => $latestExtension !== null,
                'extension_requested_at' => $latestExtension?->created_at,
                'needs_checkout' => $checkoutDueToday || $checkoutOverdue || $this->detectPaymentStatus($booking) === 'overdue',
                'checkout_due_today' => $checkoutDueToday,
                'checkout_overdue' => $checkoutOverdue,
                'needs_cleaning_after_checkout' => $needsCleaning,
                'needs_inspection' => $openComplaints > 0,
                'needs_repair' => false,
                'last_host_note' => $lastNote?->note,
                'last_activity_at' => collect([$booking->updated_at, $lastNote?->updated_at])->filter()->max(),
            ],
        )->refresh();
    }

    public function refreshForProperty(Property $property): int
    {
        $count = 0;

        Booking::query()
            ->where('property_id', $property->id)
            ->get()
            ->each(function (Booking $booking) use (&$count): void {
                $this->refreshForBooking($booking);
                $count++;
            });

        return $count;
    }

    public function refreshForGuest(User $guest): int
    {
        $count = 0;

        Booking::query()
            ->where('guest_user_id', $guest->id)
            ->get()
            ->each(function (Booking $booking) use (&$count): void {
                $this->refreshForBooking($booking);
                $count++;
            });

        return $count;
    }

    public function deleteForBooking(Booking $booking): void
    {
        HostCurrentStaySnapshot::query()
            ->where('booking_id', $booking->id)
            ->delete();
    }

    public function calculateNightsLeft(Booking $booking): int
    {
        if (! $booking->check_out_date) {
            return 0;
        }

        return (int) CarbonImmutable::today()->diffInDays(CarbonImmutable::parse($booking->check_out_date), false);
    }

    public function detectStayStatus(Booking $booking): string
    {
        $status = $this->value($booking->status);

        if ($status === BookingStatus::NoShow->value) {
            return 'no_show';
        }

        if ($this->isCancelled($booking)) {
            return 'cancelled';
        }

        if ($this->isCheckedOut($booking)) {
            return 'checked_out';
        }

        if ($this->checkoutOverdue($booking)) {
            return 'checkout_overdue';
        }

        if ($this->checkoutDueToday($booking)) {
            return 'check_out_today';
        }

        if ($this->isCheckedIn($booking) || $this->overlapsToday($booking)) {
            return 'living_now';
        }

        return 'upcoming';
    }

    public function detectPaymentStatus(Booking $booking): string
    {
        return match ($this->value($booking->payment_status)) {
            PaymentStatus::Paid->value => 'paid',
            PaymentStatus::PartiallyPaid->value => 'partial',
            PaymentStatus::Failed->value => 'overdue',
            PaymentStatus::RefundedPartial->value,
            PaymentStatus::RefundedFull->value,
            PaymentStatus::Reversed->value => 'refunded',
            default => 'pending',
        };
    }

    public function detectCheckInStatus(Booking $booking): string
    {
        if ($this->isCheckedOut($booking)) {
            return 'checked_out';
        }

        if ($this->isCheckedIn($booking)) {
            return 'checked_in';
        }

        return 'pending';
    }

    private function paidAmount(Booking $booking, float $total): float
    {
        $paid = (float) $booking->paymentRecords
            ->filter(fn ($record): bool => $this->value($record->status) === PaymentRecordStatus::Paid->value)
            ->sum(fn ($record): float => (float) $record->amount);

        if ($paid <= 0 && $this->value($booking->payment_status) === PaymentStatus::Paid->value) {
            return $total;
        }

        return $paid;
    }

    private function openComplaintsCount(Booking $booking): int
    {
        $closed = [
            ComplaintStatus::Resolved->value,
            ComplaintStatus::Closed->value,
            ComplaintStatus::Cancelled->value,
            ComplaintStatus::Dismissed->value,
        ];

        return $booking->complaints
            ->reject(fn ($complaint): bool => in_array($this->value($complaint->status), $closed, true))
            ->count();
    }

    private function latestOpenExtension(Booking $booking): mixed
    {
        $open = [
            BookingExtensionStatus::AwaitingHostApproval->value,
            BookingExtensionStatus::AwaitingPayment->value,
        ];

        return $booking->extensions
            ->filter(fn ($extension): bool => in_array($this->value($extension->status), $open, true))
            ->sortByDesc('id')
            ->first();
    }

    private function needsCleaningAfterCheckout(Booking $booking): bool
    {
        return HostCleaningTask::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', ['planned', 'needed', 'in_progress'])
            ->exists();
    }

    private function isCancelled(Booking $booking): bool
    {
        $status = $booking->status;

        return $status instanceof BookingStatus
            ? $status->isCancelled()
            : in_array((string) $status, [
                BookingStatus::CancelledByGuestFlow->value,
                BookingStatus::CancelledByHostFlow->value,
                BookingStatus::CancelledByGuest->value,
                BookingStatus::CancelledByHost->value,
                BookingStatus::CancelledBySystem->value,
                BookingStatus::CancelledByService->value,
                BookingStatus::DeclinedByHost->value,
                BookingStatus::Expired->value,
            ], true);
    }

    private function isCheckedIn(Booking $booking): bool
    {
        return $booking->checked_in_at !== null
            || in_array($this->value($booking->status), [
                BookingStatus::CheckedIn->value,
                BookingStatus::InProgress->value,
                BookingStatus::ActiveStay->value,
                BookingStatus::LeavingSoon->value,
            ], true);
    }

    private function isCheckedOut(Booking $booking): bool
    {
        return $booking->checked_out_at !== null
            || in_array($this->value($booking->status), [
                BookingStatus::CheckedOut->value,
                BookingStatus::Completed->value,
                BookingStatus::Closed->value,
            ], true);
    }

    private function checkoutDueToday(Booking $booking): bool
    {
        return ! $this->isCheckedOut($booking)
            && $booking->check_out_date
            && CarbonImmutable::parse($booking->check_out_date)->isSameDay(CarbonImmutable::today());
    }

    private function checkoutOverdue(Booking $booking): bool
    {
        return ! $this->isCheckedOut($booking)
            && $booking->check_out_date
            && CarbonImmutable::parse($booking->check_out_date)->isBefore(CarbonImmutable::today());
    }

    private function overlapsToday(Booking $booking): bool
    {
        if (! $booking->check_in_date || ! $booking->check_out_date) {
            return false;
        }

        $today = CarbonImmutable::today();

        return CarbonImmutable::parse($booking->check_in_date)->lte($today)
            && CarbonImmutable::parse($booking->check_out_date)->gte($today);
    }

    private function nightsCount(Booking $booking): int
    {
        if (! $booking->check_in_date || ! $booking->check_out_date) {
            return 0;
        }

        return (int) CarbonImmutable::parse($booking->check_in_date)
            ->diffInDays(CarbonImmutable::parse($booking->check_out_date));
    }

    private function dateString(mixed $date): ?string
    {
        return $date ? CarbonImmutable::parse($date)->toDateString() : null;
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }
}
