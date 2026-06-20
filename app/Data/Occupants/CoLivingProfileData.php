<?php

namespace App\Data\Occupants;

final readonly class CoLivingProfileData
{
    /**
     * @param  list<string>  $languages
     * @param  list<string>  $badges
     */
    public function __construct(
        public ?string $alias,
        public ?string $ageRange,
        public array $languages,
        public ?string $stayPurpose,
        public ?string $guestType,
        public ?string $sleepSchedule,
        public ?string $wakeSchedule,
        public ?string $homePresenceLevel,
        public ?bool $smokes,
        public ?string $socialLevel,
        public ?bool $prefersQuiet,
        public ?float $roommateRating,
        public int $roommateReviewsCount,
        public array $badges,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'alias' => $this->alias,
            'age_range' => $this->ageRange,
            'languages' => $this->languages,
            'stay_purpose' => $this->stayPurpose,
            'guest_type' => $this->guestType,
            'sleep_schedule' => $this->sleepSchedule,
            'wake_schedule' => $this->wakeSchedule,
            'home_presence_level' => $this->homePresenceLevel,
            'smokes' => $this->smokes,
            'social_level' => $this->socialLevel,
            'prefers_quiet' => $this->prefersQuiet,
            'roommate_rating' => $this->roommateRating,
            'roommate_reviews_count' => $this->roommateReviewsCount,
            'badges' => $this->badges,
        ];
    }
}
