<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutIssueReport;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
            'has_extra_dirty' => $checkOut->has_extra_dirty || $validated['issue_type'] === 'extra_dirty',
            'has_forgotten_items' => $checkOut->has_forgotten_items || $validated['issue_type'] === 'forgotten_items',
            'needs_deposit_deduction' => $checkOut->needs_deposit_deduction || (bool) ($validated['deposit_related'] ?? false),
            'problem_status' => 'open',
            'status' => 'problem_reported',
        ])->save();

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

    public function markResolved(User $host, BookingCheckOutIssueReport $issue): BookingCheckOutIssueReport
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
}
