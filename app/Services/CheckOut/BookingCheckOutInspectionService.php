<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\HostInspectionTask;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingCheckOutInspectionService
{
    public function startInspection(User $host, BookingCheckOut $checkOut): BookingCheckOut
    {
        $this->authorizeHost($host, $checkOut);
        $this->createInspectionTask($checkOut);

        $checkOut->forceFill([
            'inspection_required' => true,
            'status' => 'inspection_in_progress',
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'inspection_started', [
            'user_id' => $host->id,
        ]);

        return $checkOut->refresh();
    }

    public function createInspectionTask(BookingCheckOut $checkOut): HostInspectionTask
    {
        return HostInspectionTask::query()->firstOrCreate(
            ['booking_check_out_id' => $checkOut->id],
            [
                'user_id' => $checkOut->host_user_id,
                'property_id' => $checkOut->property_id,
                'room_id' => $checkOut->room_id,
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'booking_id' => $checkOut->booking_id,
                'status' => 'planned',
                'scheduled_date' => $checkOut->check_out_date,
                'scheduled_time' => $checkOut->planned_check_out_time,
                'reason' => 'after_checkout',
                'checklist_json' => [],
                'result_json' => [],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function completeInspection(User $host, BookingCheckOut $checkOut, array $data): BookingCheckOut
    {
        $this->authorizeHost($host, $checkOut);

        $checkOut->forceFill([
            'room_checked' => (bool) ($data['room_checked'] ?? true),
            'property_checked' => (bool) ($data['property_checked'] ?? true),
            'sleeping_place_checked' => (bool) ($data['sleeping_place_checked'] ?? true),
            'sleeping_place_free' => (bool) ($data['sleeping_place_free'] ?? true),
            'sleeping_place_cleared' => (bool) ($data['sleeping_place_free'] ?? true),
            'has_damage' => (bool) ($data['has_damage'] ?? false),
            'has_extra_dirty' => (bool) ($data['has_extra_dirty'] ?? false),
            'has_extra_dirt' => (bool) ($data['has_extra_dirt'] ?? $data['has_extra_dirty'] ?? false),
            'inspection_required' => false,
            'cleaning_required' => (bool) ($data['cleaning_required'] ?? $data['has_extra_dirty'] ?? $data['has_extra_dirt'] ?? false),
            'repair_required' => (bool) ($data['repair_required'] ?? $data['has_damage'] ?? false),
            'status' => (bool) ($data['has_damage'] ?? false) || (bool) ($data['has_extra_dirty'] ?? false)
                ? 'problem_reported'
                : 'inspection_completed',
        ])->save();

        $this->createInspectionTask($checkOut->refresh())->forceFill([
            'status' => 'done',
            'result_json' => $data,
            'completed_at' => now(),
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'inspection_completed', [
            'user_id' => $host->id,
        ]);

        return $checkOut->refresh();
    }

    public function detectDamage(BookingCheckOut $checkOut): bool
    {
        return (bool) $checkOut->has_damage;
    }

    public function detectCleaningNeed(BookingCheckOut $checkOut): bool
    {
        return (bool) $checkOut->has_extra_dirty || (bool) $checkOut->has_extra_dirt || $checkOut->status === 'cleaning_needed';
    }

    public function detectRepairNeed(BookingCheckOut $checkOut): bool
    {
        return $checkOut->issueReports()
            ->where('repair_needed', true)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->exists();
    }

    public function detectCleaningRequired(BookingCheckOut $checkOut): bool
    {
        return $this->detectCleaningNeed($checkOut);
    }

    public function detectRepairRequired(BookingCheckOut $checkOut): bool
    {
        return $this->detectRepairNeed($checkOut) || (bool) $checkOut->repair_required;
    }

    public function detectDepositReviewRequired(BookingCheckOut $checkOut): bool
    {
        return (bool) $checkOut->deposit_review_required
            || (bool) $checkOut->deposit_deduction_requested
            || (bool) $checkOut->needs_deposit_deduction;
    }

    private function authorizeHost(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }
    }
}
