<?php

namespace App\Data\Hints;

final readonly class HintPriorityData
{
    public function __construct(
        public string $importance,
        public int $priority,
        public string $context,
    ) {}
}
