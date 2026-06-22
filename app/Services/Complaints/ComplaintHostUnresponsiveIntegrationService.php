<?php

namespace App\Services\Complaints;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\ComplaintCase;

class ComplaintHostUnresponsiveIntegrationService
{
    public function linkHostUnresponsive(ComplaintCase $case, BookingHostUnresponsiveCase $hostUnresponsive): void
    {
        $hostUnresponsive->forceFill(['complaint_case_id' => $case->id])->save();
        $case->forceFill(['source_type' => $case->source_type ?: 'host_unresponsive', 'source_id' => $case->source_id ?: $hostUnresponsive->id])->save();
    }

    public function createFromHostUnresponsive(BookingHostUnresponsiveCase $case): ComplaintCase
    {
        return app(ComplaintCaseService::class)->createFromHostUnresponsive($case);
    }
}
