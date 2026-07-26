<?php

namespace App\Data\Occupants;

final readonly class RoomOccupantData
{
    /**
     * @param  list<string>  $languages
     * @param  list<string>  $badges
     * @param  list<string>  $lines
     * @param  list<array{label:string,value:string}>  $fields
     */
    public function __construct(
        public string $displayName,
        public ?string $alias,
        public ?string $ageRange,
        public array $languages,
        public ?string $languagesLabel,
        public ?string $checkoutDateLabel,
        public ?float $roommateRating,
        public ?string $roommateRatingLabel,
        public int $roommateReviewsCount,
        public array $badges,
        public array $lines,
        public array $fields,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'display_name' => $this->displayName,
            'alias' => $this->alias,
            'age_range' => $this->ageRange,
            'languages' => $this->languages,
            'languages_label' => $this->languagesLabel,
            'checkout_date_label' => $this->checkoutDateLabel,
            'roommate_rating' => $this->roommateRating,
            'roommate_rating_label' => $this->roommateRatingLabel,
            'roommate_reviews_count' => $this->roommateReviewsCount,
            'badges' => $this->badges,
            'lines' => $this->lines,
            'fields' => $this->fields,
        ];
    }
}
