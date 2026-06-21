<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckInProblem;

class BookingCheckInRelocationIntegrationService
{
    public function startRelocationFromCheckInProblem(BookingCheckInProblem $problem): mixed
    {
        $problem->forceFill(['status' => 'relocation_started'])->save();

        return $problem->id;
    }
}
