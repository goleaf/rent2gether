<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\HostInspectionTask;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingCheckOutInspectionService
{
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
            'sleeping_place_checked' => (bool) ($data['sleeping_place_checked'] ?? true),
            'sleeping_place_free' => (bool) ($data['sleeping_place_free'] ?? true),
            'has_damage' => (bool) ($data['has_damage'] ?? false),
            'has_extra_dirty' => (bool) ($data['has_extra_dirty'] ?? false),
            'status' => (bool) ($data['has_damage'] ?? false) || (bool) ($data['has_extra_dirty'] ?? false)
                ? 'problem_reported'
                : 'inspection_completed',
        ])->save();

        $this->createInspectionTask($checkOut->refresh())->forceFill([
            'status' => 'done',
            'result_json' => $data,
            'completed_at' => now(),
        ])->save();

        return $checkOut->refresh();
    }

    public function detectDamage(BookingCheckOut $checkOut): bool
    {
        return (bool) $checkOut->has_damage;
    }

    public function detectCleaningNeed(BookingCheckOut $checkOut): bool
    {
        return (bool) $checkOut->has_extra_dirty || $checkOut->status === 'cleaning_needed';
    }

    public function detectRepairNeed(BookingCheckOut $checkOut): bool
    {
        return $checkOut->issueReports()
            ->where('repair_needed', true)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->exists();
    }

    private function authorizeHost(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }
    }
}
