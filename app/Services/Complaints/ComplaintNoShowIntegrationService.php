<?php

namespace App\Services\Complaints;

use App\Models\BookingNoShow;
use App\Models\ComplaintCase;

class ComplaintNoShowIntegrationService
{
    public function linkNoShow(ComplaintCase $case, BookingNoShow $noShow): void
    {
        $noShow->forceFill(['complaint_case_id' => $case->id])->save();
        $case->forceFill(['source_type' => $case->source_type ?: 'no_show', 'source_id' => $case->source_id ?: $noShow->id])->save();
    }

    public function convertNoShowDisputeToComplaint(BookingNoShow $noShow): ComplaintCase
    {
        return app(ComplaintCaseService::class)->createFromNoShow($noShow);
    }
}
