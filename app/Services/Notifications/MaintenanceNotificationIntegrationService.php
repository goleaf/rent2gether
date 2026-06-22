<?php

namespace App\Services\Notifications;

class MaintenanceNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyMaintenanceReported(mixed $request): void
    {
        $this->notifyHost($this->bookingFrom($request), 'maintenance_reported');
    }

    public function notifyRepairScheduled(mixed $request): void
    {
        $this->notifyGuest($this->bookingFrom($request), 'maintenance_reported');
    }

    public function notifyRepairFixed(mixed $request): void
    {
        $this->notifyGuest($this->bookingFrom($request), 'place_ready');
    }

    public function notifyFutureBookingImpacted(mixed $impact): void
    {
        $this->notifyHost($this->bookingFrom($impact), 'maintenance_reported');
    }
}
