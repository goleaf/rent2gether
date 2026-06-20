<?php

namespace App\Data\Waitlist;

readonly class WaitlistQueuePositionData
{
    public function __construct(
        public int $position,
        public int $total,
    ) {}
}
