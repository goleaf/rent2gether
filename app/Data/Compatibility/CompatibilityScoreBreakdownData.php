<?php

namespace App\Data\Compatibility;

final readonly class CompatibilityScoreBreakdownData
{
    public function __construct(
        public int $startingScore,
        public int $positivePoints,
        public int $warningPenalty,
        public int $finalScore,
    ) {}

    /**
     * @return array{starting_score:int,positive_points:int,warning_penalty:int,final_score:int}
     */
    public function toArray(): array
    {
        return [
            'starting_score' => $this->startingScore,
            'positive_points' => $this->positivePoints,
            'warning_penalty' => $this->warningPenalty,
            'final_score' => $this->finalScore,
        ];
    }
}
