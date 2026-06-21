<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutIssue;
use App\Models\BookingCheckOutIssueReport;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class BookingCheckOutIssueService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function reportIssue(User $host, BookingCheckOut $checkOut, array $data): BookingCheckOutIssueReport
    {
        $this->authorizeHost($host, $checkOut);

        $validated = Validator::make($data, [
            'issue_type' => ['required', 'string', 'max:80'],
            'severity' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo_paths' => ['nullable', 'array', 'max:8'],
            'photo_paths.*' => ['string', 'max:255'],
            'deposit_related' => ['nullable', 'boolean'],
            'repair_needed' => ['nullable', 'boolean'],
            'cleaning_needed' => ['nullable', 'boolean'],
            'amount_requested' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ], [], trans('check_out.validation.attributes'))->validate();

        $issue = BookingCheckOutIssueReport::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $checkOut->booking_id,
            'guest_user_id' => $checkOut->guest_user_id,
            'host_user_id' => $checkOut->host_user_id,
            'issue_type' => $validated['issue_type'],
            'severity' => $validated['severity'] ?? 'medium',
            'description' => $validated['description'] ?? null,
            'photo_paths_json' => $validated['photo_paths'] ?? [],
            'status' => 'open',
            'deposit_related' => (bool) ($validated['deposit_related'] ?? false),
            'repair_needed' => (bool) ($validated['repair_needed'] ?? false),
            'cleaning_needed' => (bool) ($validated['cleaning_needed'] ?? false),
        ]);

        $checkOut->forceFill([
            'has_damage' => $checkOut->has_damage || $validated['issue_type'] === 'damage',
            'has_extra_dirty' => $checkOut->has_extra_dirty || in_array($validated['issue_type'], ['extra_dirty', 'extra_dirt'], true),
            'has_extra_dirt' => $checkOut->has_extra_dirt || $validated['issue_type'] === 'extra_dirt',
            'has_forgotten_items' => $checkOut->has_forgotten_items || $validated['issue_type'] === 'forgotten_items',
            'has_inventory_issue' => $checkOut->has_inventory_issue || in_array($validated['issue_type'], ['lost_inventory', 'damaged_inventory'], true),
            'needs_deposit_deduction' => $checkOut->needs_deposit_deduction || (bool) ($validated['deposit_related'] ?? false),
            'deposit_review_required' => $checkOut->deposit_review_required || (bool) ($validated['deposit_related'] ?? false),
            'deposit_deduction_requested' => $checkOut->deposit_deduction_requested || (float) ($validated['amount_requested'] ?? 0) > 0,
            'deposit_deduction_amount' => $validated['amount_requested'] ?? $checkOut->deposit_deduction_amount,
            'repair_required' => $checkOut->repair_required || (bool) ($validated['repair_needed'] ?? false),
            'cleaning_required' => $checkOut->cleaning_required || (bool) ($validated['cleaning_needed'] ?? false),
            'problem_status' => 'open',
            'status' => 'problem_reported',
        ])->save();

        $this->createPointTwelveIssue($checkOut->refresh(), $validated);
        $this->notifyGuest($issue);

        return $issue->refresh();
    }

    /**
     * @param  array<int, string>  $photos
     */
    public function addIssuePhotos(User $host, BookingCheckOutIssueReport $issue, array $photos): BookingCheckOutIssueReport
    {
        if ((int) $issue->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }

        $issue->forceFill([
            'photo_paths_json' => collect($issue->photo_paths_json ?? [])->merge($photos)->values()->all(),
        ])->save();

        return $issue->refresh();
    }

    public function notifyGuest(BookingCheckOutIssueReport $issue): void
    {
        $issue->forceFill(['status' => 'open'])->save();
    }

    public function markResolved(User $host, BookingCheckOutIssueReport|BookingCheckOutIssue $issue): BookingCheckOutIssueReport|BookingCheckOutIssue
    {
        if ((int) $issue->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }

        $issue->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        $checkOut = $issue->checkOut;

        if ($this->openIssues($checkOut)->isEmpty()) {
            $checkOut->forceFill(['problem_status' => 'resolved'])->save();
        }

        return $issue->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDepositRelatedIssue(BookingCheckOut $checkOut, array $data): BookingCheckOutIssueReport
    {
        return $this->reportIssue($checkOut->host, $checkOut, [
            ...$data,
            'deposit_related' => true,
        ]);
    }

    public function requestGuestResponse(BookingCheckOutIssue $issue): BookingCheckOutIssue
    {
        $issue->forceFill(['status' => 'waiting_guest_response'])->save();

        return $issue->refresh();
    }

    public function createDepositDeductionIfNeeded(BookingCheckOutIssue $issue): mixed
    {
        if ((float) $issue->amount_requested <= 0) {
            return null;
        }

        return app(BookingDepositDecisionService::class)->requestPartialDeduction(
            $issue->host,
            $issue->checkOut,
            (float) $issue->amount_requested,
            $issue->description ?: $issue->issue_type,
        );
    }

    public function createMaintenanceIfNeeded(BookingCheckOutIssue $issue): mixed
    {
        if (! in_array($issue->issue_type, ['damage', 'lost_inventory', 'damaged_inventory'], true)) {
            return null;
        }

        $issue->checkOut->forceFill([
            'repair_required' => true,
            'maintenance_request_id' => $issue->id,
        ])->save();

        $issue->forceFill([
            'status' => 'maintenance_created',
            'source_created_maintenance_request_id' => $issue->id,
        ])->save();

        return $issue->refresh();
    }

    public function createComplaintIfNeeded(BookingCheckOutIssue $issue): mixed
    {
        if (! in_array($issue->severity, ['high', 'urgent'], true)) {
            return null;
        }

        $issue->checkOut->forceFill([
            'has_complaint' => true,
            'complaint_case_id' => $issue->id,
        ])->save();

        $issue->forceFill([
            'status' => 'complaint_created',
            'source_created_complaint_case_id' => $issue->id,
        ])->save();

        return $issue->refresh();
    }

    /**
     * @return Collection<int, BookingCheckOutIssueReport>
     */
    public function openIssues(BookingCheckOut $checkOut): Collection
    {
        return $checkOut->issueReports()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get();
    }

    private function authorizeHost(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createPointTwelveIssue(BookingCheckOut $checkOut, array $validated): BookingCheckOutIssue
    {
        return BookingCheckOutIssue::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $checkOut->booking_id,
            'guest_user_id' => $checkOut->guest_user_id,
            'host_user_id' => $checkOut->host_user_id,
            'property_id' => $checkOut->property_id,
            'room_id' => $checkOut->room_id,
            'sleeping_place_id' => $checkOut->sleeping_place_id,
            'issue_type' => $validated['issue_type'],
            'severity' => $validated['severity'] ?? 'medium',
            'status' => (float) ($validated['amount_requested'] ?? 0) > 0 ? 'deposit_deduction_requested' : 'reported',
            'description' => $validated['description'] ?? null,
            'amount_requested' => $validated['amount_requested'] ?? null,
            'currency' => $validated['currency'] ?? $checkOut->booking?->currency ?? 'EUR',
        ]);
    }
}
