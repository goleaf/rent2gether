<?php

namespace App\Data\Occupants;

final readonly class RoomOccupantWarningData
{
    public function __construct(
        public string $key,
        public string $message,
        public string $tone = 'warning',
    ) {}

    /**
     * @return array{key:string,message:string,tone:string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'message' => $this->message,
            'tone' => $this->tone,
        ];
    }
}
