<?php

namespace App\Services\Disputes;

use App\Models\BookingCancellation;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingNoShow;
use App\Models\BookingRefund;
use App\Models\ComplaintCase;
use App\Models\DisputeCase;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class DisputeCaseService
{
    public function __construct(
        private readonly DisputeNumberService $numbers,
        private readonly DisputeStatusService $statuses,
        private readonly DisputeEventService $events,
        private readonly DisputeNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function openFromComplaint(User $user, ComplaintCase $complaint, array $data): DisputeCase
    {
        return DB::transaction(function () use ($user, $complaint, $data): DisputeCase {
            $dispute = $this->createDispute($user, [
                'complaint_case_id' => $complaint->id,
                'booking_id' => $complaint->booking_id,
                'booking_stay_id' => $complaint->booking_stay_id,
                'guest_user_id' => $complaint->guest_user_id,
                'host_user_id' => $complaint->host_user_id,
                'property_id' => $complaint->property_id,
                'room_id' => $complaint->room_id,
                'sleeping_place_id' => $complaint->sleeping_place_id,
                'source_type' => $data['source_type'] ?? 'complaint_case',
                'source_id' => $data['source_id'] ?? $complaint->id,
                'dispute_type' => $data['dispute_type'] ?? $this->typeFromComplaint($complaint),
                'severity' => $data['severity'] ?? $complaint->severity,
                'title' => $data['title'] ?? $complaint->title,
                'description' => $data['description'] ?? $complaint->description,
                'amount_disputed' => $data['amount_disputed'] ?? $complaint->amount_requested,
                'currency' => $data['currency'] ?? $complaint->currency,
            ]);

            $complaint->forceFill([
                'has_dispute' => true,
                'dispute_case_id' => $dispute->id,
                'status' => 'dispute_opened',
            ])->save();

            $this->events->record($dispute, 'dispute_opened', ['complaint_case_id' => $complaint->id, 'user_id' => $user->id]);
            $this->statuses->syncBookingStatus($dispute);
            $this->notifications->notifyDisputeOpened($dispute);

            return $dispute->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function openFromDeposit(User $user, mixed $depositCase, array $data): DisputeCase
    {
        return $this->createDispute($user, [
            'booking_id' => $depositCase->booking_id ?? null,
            'guest_user_id' => $depositCase->guest_user_id ?? null,
            'host_user_id' => $depositCase->host_user_id ?? null,
            'property_id' => $depositCase->property_id ?? null,
            'room_id' => $depositCase->room_id ?? null,
            'sleeping_place_id' => $depositCase->sleeping_place_id ?? null,
            'source_type' => 'deposit_case',
            'source_id' => $depositCase->id ?? null,
            'deposit_case_id' => $depositCase->id ?? null,
            'dispute_type' => $data['dispute_type'] ?? 'deposit_dispute',
            'amount_disputed' => $data['amount_disputed'] ?? $depositCase->amount ?? 0,
            'currency' => $data['currency'] ?? 'EUR',
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function openFromCancellation(User $user, BookingCancellation $cancellation, array $data): DisputeCase
    {
        return $this->createDispute($user, [
            'booking_id' => $cancellation->booking_id,
            'guest_user_id' => $cancellation->guest_user_id,
            'host_user_id' => $cancellation->host_user_id,
            'property_id' => $cancellation->property_id,
            'room_id' => $cancellation->room_id,
            'sleeping_place_id' => $cancellation->sleeping_place_id,
            'source_type' => 'booking_cancellation',
            'source_id' => $cancellation->id,
            'booking_cancellation_id' => $cancellation->id,
            'dispute_type' => $data['dispute_type'] ?? 'cancellation_dispute',
            'amount_disputed' => $data['amount_disputed'] ?? $cancellation->total_refund_amount,
            'currency' => $data['currency'] ?? $cancellation->currency,
            'description' => $data['description'] ?? $cancellation->comment,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function openFromRefund(User $user, BookingRefund $refund, array $data): DisputeCase
    {
        return $this->createDispute($user, [
            'booking_id' => $refund->booking_id,
            'guest_user_id' => $refund->guest_user_id,
            'host_user_id' => $refund->host_user_id,
            'property_id' => $refund->property_id,
            'room_id' => $refund->room_id,
            'sleeping_place_id' => $refund->sleeping_place_id,
            'source_type' => 'booking_refund',
            'source_id' => $refund->id,
            'booking_refund_id' => $refund->id,
            'dispute_type' => $data['dispute_type'] ?? 'refund_dispute',
            'amount_disputed' => $data['amount_disputed'] ?? $refund->amount,
            'currency' => $data['currency'] ?? $refund->currency,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function openFromNoShow(User $user, BookingNoShow $noShow, array $data): DisputeCase
    {
        return $this->createDispute($user, [
            'booking_id' => $noShow->booking_id,
            'guest_user_id' => $noShow->guest_user_id,
            'host_user_id' => $noShow->host_user_id,
            'property_id' => $noShow->property_id,
            'room_id' => $noShow->room_id,
            'sleeping_place_id' => $noShow->sleeping_place_id,
            'source_type' => 'booking_no_show',
            'source_id' => $noShow->id,
            'booking_no_show_id' => $noShow->id,
            'dispute_type' => $data['dispute_type'] ?? 'no_show_dispute',
            'amount_disputed' => $data['amount_disputed'] ?? $noShow->penalty_amount,
            'currency' => $data['currency'] ?? $noShow->currency,
            'description' => $data['description'] ?? $noShow->guest_comment,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function openFromHostUnresponsive(User $user, BookingHostUnresponsiveCase $case, array $data): DisputeCase
    {
        return $this->createDispute($user, [
            'booking_id' => $case->booking_id,
            'guest_user_id' => $case->guest_user_id,
            'host_user_id' => $case->host_user_id,
            'property_id' => $case->property_id,
            'room_id' => $case->room_id,
            'sleeping_place_id' => $case->sleeping_place_id,
            'source_type' => 'host_unresponsive_case',
            'source_id' => $case->id,
            'host_unresponsive_case_id' => $case->id,
            'dispute_type' => $data['dispute_type'] ?? 'host_unresponsive_dispute',
            'amount_disputed' => $data['amount_disputed'] ?? $case->refund_amount,
            'currency' => $data['currency'] ?? $case->currency,
            'description' => $data['description'] ?? $case->guest_comment,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, DisputeCase>
     */
    public function getForGuest(User $guest, array $filters = []): CursorPaginator
    {
        return DisputeCase::query()
            ->select($this->listColumns())
            ->where('guest_user_id', $guest->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, DisputeCase>
     */
    public function getForHost(User $host, array $filters = []): CursorPaginator
    {
        return DisputeCase::query()
            ->select($this->listColumns())
            ->where('host_user_id', $host->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    public function closeDispute(DisputeCase $dispute): DisputeCase
    {
        $dispute = $this->statuses->transition($dispute, 'closed');
        $this->events->record($dispute, 'dispute_closed');
        $this->notifications->notifyDisputeClosed($dispute);

        return $dispute->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createDispute(User $user, array $data): DisputeCase
    {
        $dispute = DisputeCase::query()->create([
            'dispute_number' => $this->numbers->generate(),
            'complaint_case_id' => $data['complaint_case_id'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'booking_stay_id' => $data['booking_stay_id'] ?? null,
            'guest_user_id' => $data['guest_user_id'] ?? null,
            'host_user_id' => $data['host_user_id'] ?? null,
            'opened_by_user_id' => $user->id,
            'property_id' => $data['property_id'] ?? null,
            'room_id' => $data['room_id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? null,
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'dispute_type' => $data['dispute_type'] ?? 'other',
            'severity' => $data['severity'] ?? 'medium',
            'status' => 'opened',
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'amount_disputed' => $data['amount_disputed'] ?? 0,
            'currency' => $data['currency'] ?? 'EUR',
            'booking_refund_id' => $data['booking_refund_id'] ?? null,
            'deposit_case_id' => $data['deposit_case_id'] ?? null,
            'booking_cancellation_id' => $data['booking_cancellation_id'] ?? null,
            'booking_relocation_id' => $data['booking_relocation_id'] ?? null,
            'booking_no_show_id' => $data['booking_no_show_id'] ?? null,
            'host_unresponsive_case_id' => $data['host_unresponsive_case_id'] ?? null,
            'mismatch_report_id' => $data['mismatch_report_id'] ?? null,
        ]);

        $this->events->record($dispute, 'dispute_opened', ['user_id' => $user->id]);
        $this->notifications->notifyDisputeOpened($dispute);

        return $dispute->fresh();
    }

    private function typeFromComplaint(ComplaintCase $complaint): string
    {
        return match ($complaint->complaint_type) {
            'host_unresponsive' => 'host_unresponsive_dispute',
            'listing_mismatch' => 'listing_mismatch_dispute',
            'payment_problem' => 'payment_dispute',
            'refund_problem' => 'refund_dispute',
            'deposit_problem' => 'deposit_dispute',
            'cancellation_problem' => 'cancellation_dispute',
            'relocation_problem' => 'relocation_dispute',
            'property_damage' => 'damage_dispute',
            'unsafe_situation' => 'safety_dispute',
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
            'dispute_number',
            'complaint_case_id',
            'booking_id',
            'guest_user_id',
            'host_user_id',
            'opened_by_user_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'dispute_type',
            'severity',
            'status',
            'amount_disputed',
            'currency',
            'created_at',
        ];
    }
}
