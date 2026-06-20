<?php

namespace App\Data\Favorites;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final readonly class FavoriteContext
{
    public function __construct(
        public ?int $collectionId = null,
        public ?string $source = null,
        public CarbonImmutable|string|null $checkIn = null,
        public CarbonImmutable|string|null $checkOut = null,
        public int $guestsCount = 1,
        public ?string $personalNote = null,
        public ?string $shortLabel = null,
        public ?string $labelColor = null,
        public string $priority = 'normal',
        public string $decisionStatus = 'saved',
        public bool $notifyPriceDrop = true,
        public bool $notifyPriceIncrease = false,
        public bool $notifyAvailableAgain = true,
        public bool $notifyUnavailable = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            collectionId: isset($data['collection_id']) ? (int) $data['collection_id'] : null,
            source: isset($data['source']) ? (string) $data['source'] : null,
            checkIn: $data['check_in'] ?? null,
            checkOut: $data['check_out'] ?? null,
            guestsCount: max(1, (int) ($data['guests_count'] ?? 1)),
            personalNote: isset($data['personal_note']) ? (string) $data['personal_note'] : null,
            shortLabel: isset($data['short_label']) ? (string) $data['short_label'] : null,
            labelColor: isset($data['label_color']) ? (string) $data['label_color'] : null,
            priority: (string) ($data['priority'] ?? 'normal'),
            decisionStatus: (string) ($data['decision_status'] ?? 'saved'),
            notifyPriceDrop: (bool) ($data['notify_price_drop'] ?? true),
            notifyPriceIncrease: (bool) ($data['notify_price_increase'] ?? false),
            notifyAvailableAgain: (bool) ($data['notify_available_again'] ?? true),
            notifyUnavailable: (bool) ($data['notify_unavailable'] ?? true),
        );
    }

    public function checkInDate(): ?CarbonImmutable
    {
        return $this->date($this->checkIn);
    }

    public function checkOutDate(): ?CarbonImmutable
    {
        return $this->date($this->checkOut);
    }

    private function date(CarbonImmutable|CarbonInterface|string|null $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof CarbonInterface
            ? CarbonImmutable::instance($value)->startOfDay()
            : CarbonImmutable::parse($value)->startOfDay();
    }
}
