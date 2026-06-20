<?php

namespace App\Data\Occupants;

final readonly class RoomOccupantSummaryData
{
    /**
     * @param  list<string>  $badges
     * @param  list<string>  $messages
     * @param  list<RoomOccupantWarningData>  $warnings
     * @param  list<RoomOccupantData>  $cards
     */
    public function __construct(
        public int $occupantsCount,
        public array $badges,
        public array $messages,
        public array $warnings,
        public array $cards,
        public string $privacyNote,
        public bool $confirmed = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'occupants_count' => $this->occupantsCount,
            'badges' => $this->badges,
            'messages' => $this->messages,
            'warnings' => array_map(fn (RoomOccupantWarningData $warning): array => $warning->toArray(), $this->warnings),
            'cards' => array_map(fn (RoomOccupantData $card): array => $card->toArray(), $this->cards),
            'privacy_note' => $this->privacyNote,
            'confirmed' => $this->confirmed,
        ];
    }
}
