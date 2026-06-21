<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;

class ListingMismatchMaintenanceIntegrationService
{
    public function createMaintenanceIfNeeded(BookingListingMismatchReport $report): mixed
    {
        if (! in_array($report->mismatch_type, ['missing_hot_water', 'missing_heating', 'missing_air_conditioning', 'wifi_not_working', 'access_mismatch', 'safety_mismatch', 'mold'], true)) {
            if (! in_array($report->severity, ['urgent', 'unsafe'], true)) {
                return null;
            }
        }

        $maintenanceId = $report->maintenance_request_id ?: $report->id;

        $report->forceFill([
            'maintenance_request_id' => $maintenanceId,
            'resolution_type' => $report->resolution_type ?: 'repair',
            'resolution_status' => 'in_progress',
        ])->save();

        app(ListingMismatchEventService::class)->record($report->fresh(), 'maintenance_created', ['maintenance_request_id' => $maintenanceId]);

        return $report->fresh();
    }

    public function markMaintenanceResolutionCompleted(BookingListingMismatchReport $report): void
    {
        $report->forceFill([
            'status' => 'fixed',
            'resolution_status' => 'completed',
            'resolved_at' => now(),
        ])->save();
    }
}
