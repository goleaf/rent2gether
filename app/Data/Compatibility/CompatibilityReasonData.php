<?php

namespace App\Data\Compatibility;

final readonly class CompatibilityReasonData
{
    public function __construct(
        public string $key,
        public string $message,
        public int $weight = 0,
        public string $severity = 'info',
    ) {}

    /**
     * @return array{key:string,message:string,weight:int,severity:string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'message' => $this->message,
            'weight' => $this->weight,
            'severity' => $this->severity,
        ];
    }
}
