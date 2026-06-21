<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCheckInProblem;
use App\Models\BookingListingMismatchReport;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ListingMismatchService
{
    public function __construct(
        private readonly ListingMismatchNumberService $numbers,
        private readonly ListingMismatchPrivacyService $privacy,
        private readonly ListingMismatchItemService $items,
        private readonly ListingMismatchSnapshotCompareService $snapshotCompare,
        private readonly ListingMismatchWarningService $warnings,
        private readonly ListingMismatchStatusService $statuses,
        private readonly ListingMismatchEventService $events,
        private readonly ListingMismatchNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromGuestReport(User $guest, Booking $booking, array $data): BookingListingMismatchReport
    {
        $booking = $this->bookingForReport($booking);

        if (! $this->privacy->canGuestCreate($guest, $booking) || ! $this->bookingCanReceiveReport($booking)) {
            throw ValidationException::withMessages([
                'booking' => __('listing_mismatch.validation.cannot_report'),
            ]);
        }

        return $this->createReport($guest, $booking, [
            ...$data,
            'source_type' => $data['source_type'] ?? 'guest_report',
            'source_id' => $data['source_id'] ?? null,
        ]);
    }

    public function createFromCheckInProblem(BookingCheckInProblem $problem): BookingListingMismatchReport
    {
        $problem->loadMissing('guest', 'booking');

        if (! $problem->guest instanceof User || ! $problem->booking instanceof Booking) {
            throw ValidationException::withMessages([
                'booking' => __('listing_mismatch.validation.cannot_report'),
            ]);
        }

        $report = $this->createFromGuestReport($problem->guest, $problem->booking, [
            'source_type' => 'check_in_problem',
            'source_id' => $problem->id,
            'mismatch_type' => $this->mismatchTypeFromProblem((string) $problem->problem_type),
            'severity' => $problem->severity ?: 'high',
            'guest_description' => $problem->description,
            'guest_wants_relocation' => $problem->guest_wants_relocation,
            'guest_wants_cancellation' => $problem->guest_wants_cancellation,
            'guest_wants_refund' => $problem->guest_wants_refund,
        ]);

        $problem->forceFill([
            'source_created_mismatch_report_id' => $report->id,
            'status' => 'mismatch_reported',
        ])->save();

        return $report->fresh();
    }

    public function createFromCancellation(BookingCancellation $cancellation): BookingListingMismatchReport
    {
        $cancellation->loadMissing('booking', 'guest');
        $booking = $cancellation->booking;

        if (! $booking instanceof Booking || ! $cancellation->guest instanceof User) {
            throw ValidationException::withMessages([
                'booking' => __('listing_mismatch.validation.cannot_report'),
            ]);
        }

        return $this->createReport($cancellation->guest, $this->bookingForReport($booking), [
            'source_type' => 'cancellation',
            'source_id' => $cancellation->id,
            'mismatch_type' => 'other',
            'severity' => 'high',
            'guest_description' => $cancellation->comment,
            'guest_wants_cancellation' => true,
            'guest_wants_refund' => true,
        ]);
    }

    public function createFromComplaint(mixed $complaint): BookingListingMismatchReport
    {
        $complaint->loadMissing('booking', 'reporterUser');

        return $this->createReport($complaint->reporterUser, $this->bookingForReport($complaint->booking), [
            'source_type' => 'complaint',
            'source_id' => $complaint->id,
            'mismatch_type' => 'other',
            'severity' => $complaint->priority ?? 'high',
            'guest_description' => $complaint->description,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, BookingListingMismatchReport>
     */
    public function getForGuest(User $guest, array $filters = []): CursorPaginator
    {
        return BookingListingMismatchReport::query()
            ->select($this->listColumns())
            ->with(['sleepingPlace:id,display_name', 'room:id,title,name'])
            ->where('guest_user_id', $guest->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, BookingListingMismatchReport>
     */
    public function getForHost(User $host, array $filters = []): CursorPaginator
    {
        return BookingListingMismatchReport::query()
            ->select($this->listColumns())
            ->with(['guest:id,name', 'sleepingPlace:id,display_name', 'room:id,title,name'])
            ->where('host_user_id', $host->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    public function closeReport(BookingListingMismatchReport $report): BookingListingMismatchReport
    {
        $closed = $this->statuses->transition($report, 'closed');
        $this->events->record($closed, 'mismatch_closed');
        $this->notifications->notifyCaseClosed($closed);

        return $closed;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createReport(User $guest, Booking $booking, array $data): BookingListingMismatchReport
    {
        return DB::transaction(function () use ($guest, $booking, $data): BookingListingMismatchReport {
            $report = BookingListingMismatchReport::query()->create([
                'mismatch_number' => $this->numbers->generate(),
                'booking_id' => $booking->id,
                'booking_stay_id' => $booking->stay?->id,
                'booking_check_in_id' => $booking->checkIn?->id,
                'booking_check_out_id' => $booking->checkOut?->id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'source_type' => $data['source_type'] ?? 'guest_report',
                'source_id' => $data['source_id'] ?? null,
                'mismatch_type' => $data['mismatch_type'] ?? 'other',
                'severity' => $data['severity'] ?? 'medium',
                'status' => 'reported',
                'reported_at' => now(),
                'discovered_at' => $data['discovered_at'] ?? now(),
                'guest_description' => $data['guest_description'] ?? $data['description'] ?? null,
                'what_was_promised' => $data['what_was_promised'] ?? null,
                'what_was_actual' => $data['what_was_actual'] ?? null,
                'guest_wants_to_stay' => (bool) ($data['guest_wants_to_stay'] ?? false),
                'guest_wants_fix' => (bool) ($data['guest_wants_fix'] ?? true),
                'guest_wants_relocation' => (bool) ($data['guest_wants_relocation'] ?? false),
                'guest_wants_cancellation' => (bool) ($data['guest_wants_cancellation'] ?? false),
                'guest_wants_refund' => (bool) ($data['guest_wants_refund'] ?? false),
                'guest_wants_compensation' => (bool) ($data['guest_wants_compensation'] ?? false),
                'resolution_status' => 'not_started',
                'currency' => $booking->currency ?: 'EUR',
                'future_review_required' => (bool) ($data['future_review_required'] ?? false),
                'future_review_comment' => $data['future_review_comment'] ?? null,
            ]);

            $items = $data['items'] ?? [[
                'item_key' => $data['item_key'] ?? $report->mismatch_type,
                'item_type' => $data['item_type'] ?? 'other',
                'severity' => $report->severity,
            ]];

            $this->items->createItemsFromReport($report, $items);
            $this->snapshotCompare->compareWithBookingSnapshot($report->fresh());
            $this->warnings->generateWarnings($report->fresh());
            $this->events->record($report->fresh(), 'mismatch_reported', ['user_id' => $guest->id]);
            $this->notifications->notifyHostMismatchReported($report->fresh());
            $report = $this->statuses->transition($report->fresh(), 'host_notified', $guest);

            return $this->statuses->transition($report, 'waiting_host_response', $guest);
        });
    }

    private function bookingForReport(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'booking_number',
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
                'arrival_time',
                'nights_count',
                'total',
                'total_amount',
                'total_payable',
                'currency',
                'nightly_price_snapshot',
            ])
            ->with(['checkIn', 'stay', 'checkOut', 'sleepingPlace:id,property_id,room_id,user_id,currency'])
            ->findOrFail($booking->id);
    }

    private function bookingCanReceiveReport(Booking $booking): bool
    {
        $status = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;

        return in_array($status, [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckInCore->value,
            BookingStatus::GuestCheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::StayInProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::GuestCheckedOut->value,
            BookingStatus::Completed->value,
        ], true);
    }

    private function mismatchTypeFromProblem(string $problemType): string
    {
        return match ($problemType) {
            'wrong_address' => 'wrong_address',
            'dirty', 'cleanliness', 'dirty_room' => 'dirty_room',
            'access_problem' => 'access_mismatch',
            default => 'other',
        };
    }

    /**
     * @return list<string>
     */
    private function listColumns(): array
    {
        return [
            'id',
            'mismatch_number',
            'booking_id',
            'guest_user_id',
            'host_user_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'mismatch_type',
            'severity',
            'status',
            'reported_at',
            'resolution_type',
            'resolution_status',
            'refund_amount',
            'compensation_amount',
            'currency',
            'created_at',
        ];
    }
}
