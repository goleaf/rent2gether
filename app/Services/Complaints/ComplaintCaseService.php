<?php

namespace App\Services\Complaints;

use App\Models\Booking;
use App\Models\BookingCheckInProblem;
use App\Models\BookingCheckOutIssue;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingListingMismatchReport;
use App\Models\BookingNoShow;
use App\Models\ComplaintCase;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ComplaintCaseService
{
    public function __construct(
        private readonly ComplaintNumberService $numbers,
        private readonly ComplaintPrivacyService $privacy,
        private readonly ComplaintPartyService $parties,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
        private readonly ComplaintNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromGuest(User $guest, Booking $booking, array $data): ComplaintCase
    {
        if (! $this->privacy->canGuestCreate($guest, $booking)) {
            throw ValidationException::withMessages([
                'booking' => __('complaints.validation.cannot_create'),
            ]);
        }

        return $this->createFromBooking($guest, $booking, [
            ...$data,
            'submitted_by_type' => 'guest',
            'against_type' => $data['against_type'] ?? 'host',
            'against_user_id' => $data['against_user_id'] ?? $booking->host_user_id,
            'source_type' => $data['source_type'] ?? 'guest_report',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromHost(User $host, Booking $booking, array $data): ComplaintCase
    {
        if (! $this->privacy->canHostCreate($host, $booking)) {
            throw ValidationException::withMessages([
                'booking' => __('complaints.validation.cannot_create'),
            ]);
        }

        return $this->createFromBooking($host, $booking, [
            ...$data,
            'submitted_by_type' => 'host',
            'against_type' => $data['against_type'] ?? 'guest',
            'against_user_id' => $data['against_user_id'] ?? $booking->guest_user_id,
            'source_type' => $data['source_type'] ?? 'host_report',
        ]);
    }

    public function createFromMismatch(BookingListingMismatchReport $mismatch): ComplaintCase
    {
        $mismatch->loadMissing('booking', 'guest');

        return $this->createFromBooking($mismatch->guest, $mismatch->booking, [
            'submitted_by_type' => 'guest',
            'against_type' => 'host',
            'against_user_id' => $mismatch->host_user_id,
            'source_type' => 'listing_mismatch_report',
            'source_id' => $mismatch->id,
            'complaint_type' => 'listing_mismatch',
            'severity' => $mismatch->severity ?: 'high',
            'description' => $mismatch->guest_description ?: __('complaints.messages.created_from_mismatch'),
            'desired_resolution_type' => 'fix_problem',
            'guest_wants_refund' => $mismatch->guest_wants_refund,
            'guest_wants_relocation' => $mismatch->guest_wants_relocation,
            'guest_wants_cancellation' => $mismatch->guest_wants_cancellation,
            'guest_wants_compensation' => $mismatch->guest_wants_compensation,
        ]);
    }

    public function createFromCheckInProblem(BookingCheckInProblem $problem): ComplaintCase
    {
        $problem->loadMissing('booking', 'guest');

        return $this->createFromBooking($problem->guest, $problem->booking, [
            'submitted_by_type' => 'guest',
            'against_type' => 'host',
            'against_user_id' => $problem->host_user_id,
            'source_type' => 'check_in_problem',
            'source_id' => $problem->id,
            'complaint_type' => 'check_in_problem',
            'severity' => $problem->severity ?: 'high',
            'description' => $problem->description ?: __('complaints.messages.created_from_check_in'),
            'desired_resolution_type' => 'fix_problem',
        ]);
    }

    public function createFromCheckoutIssue(BookingCheckOutIssue $issue): ComplaintCase
    {
        $issue->loadMissing('booking', 'guest');

        return $this->createFromBooking($issue->guest, $issue->booking, [
            'submitted_by_type' => 'guest',
            'against_type' => 'host',
            'against_user_id' => $issue->host_user_id,
            'source_type' => 'checkout_issue',
            'source_id' => $issue->id,
            'complaint_type' => $issue->issue_type === 'damage' ? 'property_damage' : 'other',
            'severity' => $issue->severity ?: 'medium',
            'description' => $issue->description ?: __('complaints.messages.created_from_checkout'),
            'desired_resolution_type' => 'fix_problem',
        ]);
    }

    public function createFromNoShow(BookingNoShow $noShow): ComplaintCase
    {
        $noShow->loadMissing('booking', 'guest');

        return $this->createFromBooking($noShow->guest, $noShow->booking, [
            'submitted_by_type' => 'guest',
            'against_type' => 'host',
            'against_user_id' => $noShow->host_user_id,
            'source_type' => 'no_show',
            'source_id' => $noShow->id,
            'complaint_type' => 'cancellation_problem',
            'severity' => 'high',
            'description' => __('complaints.messages.created_from_no_show'),
            'desired_resolution_type' => 'message_or_explanation',
        ]);
    }

    public function createFromHostUnresponsive(BookingHostUnresponsiveCase $case): ComplaintCase
    {
        $case->loadMissing('booking', 'guest');

        return $this->createFromBooking($case->guest, $case->booking, [
            'submitted_by_type' => 'guest',
            'against_type' => 'host',
            'against_user_id' => $case->host_user_id,
            'source_type' => 'host_unresponsive',
            'source_id' => $case->id,
            'complaint_type' => 'host_unresponsive',
            'severity' => 'urgent',
            'description' => $case->guest_comment ?: __('complaints.messages.created_from_host_unresponsive'),
            'desired_resolution_type' => 'message_or_explanation',
            'guest_wants_refund' => $case->guest_wants_refund,
            'guest_wants_relocation' => $case->guest_wants_relocation,
            'guest_wants_cancellation' => $case->guest_wants_cancellation,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, ComplaintCase>
     */
    public function getForGuest(User $guest, array $filters = []): CursorPaginator
    {
        return ComplaintCase::query()
            ->select($this->listColumns())
            ->with(['sleepingPlace:id,display_name', 'room:id,title,name'])
            ->where(function ($query) use ($guest): void {
                $query->where('guest_user_id', $guest->id)
                    ->orWhere('reporter_user_id', $guest->id)
                    ->orWhere('against_user_id', $guest->id);
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, ComplaintCase>
     */
    public function getForHost(User $host, array $filters = []): CursorPaginator
    {
        return ComplaintCase::query()
            ->select($this->listColumns())
            ->with(['guest:id,name', 'sleepingPlace:id,display_name', 'room:id,title,name'])
            ->where(function ($query) use ($host): void {
                $query->where('host_user_id', $host->id)
                    ->orWhere('reporter_user_id', $host->id)
                    ->orWhere('against_user_id', $host->id);
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    public function closeComplaint(ComplaintCase $case): ComplaintCase
    {
        $case = $this->statuses->transition($case, 'closed');
        $this->events->record($case, 'complaint_closed');
        $this->notifications->notifyComplaintClosed($case);

        return $case->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createFromBooking(User $reporter, Booking $booking, array $data): ComplaintCase
    {
        $booking->loadMissing('stay', 'checkIn', 'checkOut');

        return DB::transaction(function () use ($reporter, $booking, $data): ComplaintCase {
            $case = ComplaintCase::query()->create([
                'complaint_number' => $this->numbers->generate(),
                'booking_id' => $booking->id,
                'booking_stay_id' => $booking->stay?->id,
                'booking_check_in_id' => $booking->checkIn?->id,
                'booking_check_out_id' => $booking->checkOut?->id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'reporter_user_id' => $reporter->id,
                'against_user_id' => $data['against_user_id'] ?? null,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'submitted_by_type' => $data['submitted_by_type'],
                'against_type' => $data['against_type'] ?? 'unknown',
                'complaint_type' => $data['complaint_type'] ?? 'other',
                'severity' => $data['severity'] ?? 'medium',
                'status' => 'submitted',
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? __('complaints.messages.default_description'),
                'desired_resolution_type' => $data['desired_resolution_type'] ?? null,
                'resolution_status' => 'not_started',
                'guest_wants_refund' => (bool) ($data['guest_wants_refund'] ?? false),
                'guest_wants_relocation' => (bool) ($data['guest_wants_relocation'] ?? false),
                'guest_wants_cancellation' => (bool) ($data['guest_wants_cancellation'] ?? false),
                'guest_wants_compensation' => (bool) ($data['guest_wants_compensation'] ?? false),
                'host_wants_deposit_deduction' => (bool) ($data['host_wants_deposit_deduction'] ?? false),
                'host_wants_guest_warning_future' => (bool) ($data['host_wants_guest_warning_future'] ?? false),
                'host_wants_payment_resolution' => (bool) ($data['host_wants_payment_resolution'] ?? false),
                'amount_requested' => $data['amount_requested'] ?? 0,
                'currency' => $data['currency'] ?? $booking->currency ?: 'EUR',
                'future_review_required' => (bool) ($data['future_review_required'] ?? (($data['severity'] ?? 'medium') === 'emergency')),
                'future_review_comment' => $data['future_review_comment'] ?? null,
            ]);

            $this->parties->createParties($case);
            $this->events->record($case, 'complaint_submitted', ['user_id' => $reporter->id]);
            $this->notifications->notifyComplaintSubmitted($case->fresh());
            $case->forceFill(['other_party_notified_at' => now()])->save();
            $this->parties->notifyOtherParty($case->fresh());
            $this->events->record($case->fresh(), 'other_party_notified');

            if (Schema::hasColumn('bookings', 'has_complaint')) {
                $booking->forceFill(['has_complaint' => true])->save();
            }

            return $this->statuses->transition($case->fresh(), 'waiting_other_party_response', $reporter);
        });
    }

    /**
     * @return list<string>
     */
    private function listColumns(): array
    {
        return [
            'id',
            'complaint_number',
            'booking_id',
            'guest_user_id',
            'host_user_id',
            'reporter_user_id',
            'against_user_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'complaint_type',
            'severity',
            'status',
            'desired_resolution_type',
            'created_at',
        ];
    }
}
