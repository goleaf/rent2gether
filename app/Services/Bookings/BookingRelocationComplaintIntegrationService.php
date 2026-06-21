<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;

class BookingRelocationComplaintIntegrationService
{
    public function __construct(
        private readonly BookingRelocationEventService $events,
    ) {}

    public function linkToComplaint(BookingRelocation $relocation, mixed $complaint): void
    {
        $relocation->forceFill([
            'source_type' => 'complaint_case',
            'source_id' => $complaint->id ?? null,
        ])->save();

        $this->events->record($relocation->refresh(), 'relocation_requested', [
            'source_type' => 'complaint_case',
            'source_id' => $complaint->id ?? null,
        ]);
    }

    public function markComplaintResolutionOffered(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'relocation_scheduled', ['complaint_resolution_offered' => true]);
    }

    public function markComplaintResolvedIfAllowed(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'relocation_applied', ['complaint_resolution_applied' => true]);
    }
}
