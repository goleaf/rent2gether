<?php

namespace App\Data\Occupants;

final readonly class RoomOccupantData
{
    /**
     * @param  list<string>  $languages
     * @param  list<string>  $badges
     * @param  list<string>  $lines
     */
    public function __construct(
        public int $snapshotId,
        public int $userId,
        public int $bookingId,
        public ?string $alias,
        public ?string $ageRange,
        public array $languages,
        public ?string $checkoutDateLabel,
        public ?float $roommateRating,
        public int $roommateReviewsCount,
        public array $badges,
        public array $lines,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'snapshot_id' => $this->snapshotId,
            'user_id' => $this->userId,
            'booking_id' => $this->bookingId,
            'alias' => $this->alias,
            'age_range' => $this->ageRange,
            'languages' => $this->languages,
            'checkout_date_label' => $this->checkoutDateLabel,
            'roommate_rating' => $this->roommateRating,
            'roommate_reviews_count' => $this->roommateReviewsCount,
            'badges' => $this->badges,
            'lines' => $this->lines,
        ];
    }
}
