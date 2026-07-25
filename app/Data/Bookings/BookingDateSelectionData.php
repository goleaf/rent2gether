<?php

namespace App\Data\Bookings;

final readonly class BookingDateSelectionData
{
    public function __construct(
        public string $checkInDate,
        public ?string $checkInTime,
        public string $checkOutDate,
        public ?string $checkOutTime,
        public int $nightsCount,
        public int $stayDaysCount,
        public int $calendarPresenceDaysCount,
        public bool $earlyCheckInRequested,
        public bool $lateCheckOutRequested,
        public bool $flexibleCheckIn,
        public bool $flexibleCheckOut,
        public bool $requiresHostTimeApproval,
        public ?string $checkInComment,
        public ?string $checkOutComment,
    ) {}

    /**
     * @return array{
     *     check_in_date:string,
     *     check_in_time:?string,
     *     check_out_date:string,
     *     check_out_time:?string,
     *     nights_count:int,
     *     stay_days_count:int,
     *     chargeable_days_count:int,
     *     calendar_presence_days_count:int,
     *     early_check_in_requested:bool,
     *     late_check_out_requested:bool,
     *     flexible_check_in:bool,
     *     flexible_check_out:bool,
     *     requires_host_time_approval:bool,
     *     check_in_comment:?string,
     *     check_out_comment:?string
     * }
     */
    public function toArray(): array
    {
        return [
            'check_in_date' => $this->checkInDate,
            'check_in_time' => $this->checkInTime,
            'check_out_date' => $this->checkOutDate,
            'check_out_time' => $this->checkOutTime,
            'nights_count' => $this->nightsCount,
            'stay_days_count' => $this->stayDaysCount,
            'chargeable_days_count' => $this->stayDaysCount,
            'calendar_presence_days_count' => $this->calendarPresenceDaysCount,
            'early_check_in_requested' => $this->earlyCheckInRequested,
            'late_check_out_requested' => $this->lateCheckOutRequested,
            'flexible_check_in' => $this->flexibleCheckIn,
            'flexible_check_out' => $this->flexibleCheckOut,
            'requires_host_time_approval' => $this->requiresHostTimeApproval,
            'check_in_comment' => $this->checkInComment,
            'check_out_comment' => $this->checkOutComment,
        ];
    }
}
