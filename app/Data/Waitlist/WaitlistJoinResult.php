<?php

namespace App\Data\Waitlist;

use App\Models\WaitlistItem;

readonly class WaitlistJoinResult
{
    public function __construct(
        public WaitlistItem $item,
        public int $position,
        public bool $alreadyJoined = false,
    ) {}
}
