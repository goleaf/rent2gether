<?php

namespace App\Data\Occupants;

final readonly class RoommateCompatibilityData
{
    /**
     * @param  list<RoomOccupantWarningData>  $warnings
     * @param  list<string>  $messages
     */
    public function __construct(
        public int $score,
        public array $warnings,
        public array $messages,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'warnings' => array_map(fn (RoomOccupantWarningData $warning): array => $warning->toArray(), $this->warnings),
            'messages' => $this->messages,
        ];
    }
}
