<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckInProblem;

class BookingCheckInComplaintIntegrationService
{
    public function createCaseFromCheckInProblem(BookingCheckInProblem $problem): mixed
    {
        $problem->forceFill([
            'source_created_complaint_case_id' => $problem->id,
            'status' => 'complaint_created',
        ])->save();

        $problem->booking()->update(['has_complaint' => true]);

        return $problem->id;
    }
}
