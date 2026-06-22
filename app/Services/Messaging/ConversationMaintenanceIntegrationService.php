<?php

namespace App\Services\Messaging;

class ConversationMaintenanceIntegrationService
{
    public function addMaintenanceReportedEvent(mixed $request): void
    {
        $this->add($request, 'maintenance_reported', 'important');
    }

    public function addMaintenanceFixedEvent(mixed $request): void
    {
        $this->add($request, 'maintenance_fixed');
    }

    private function add(mixed $request, string $eventKey, string $importance = 'normal'): void
    {
        if (! method_exists($request, 'booking')) {
            return;
        }

        app(ConversationSystemEventService::class)->addBookingEvent($request->booking()->firstOrFail(), $eventKey, [
            'source_type' => 'maintenance',
            'source_id' => $request->id ?? null,
            'importance_level' => $importance,
        ]);
    }
}
