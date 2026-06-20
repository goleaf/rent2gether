<?php

namespace App\Data\Listings;

final readonly class ListingCardBadgeData
{
    public function __construct(
        public string $key,
        public string $label,
        public string $tone = 'zinc',
        public ?string $icon = null,
    ) {}

    /**
     * @return array{key:string,label:string,tone:string,icon:?string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'tone' => $this->tone,
            'icon' => $this->icon,
        ];
    }
}
