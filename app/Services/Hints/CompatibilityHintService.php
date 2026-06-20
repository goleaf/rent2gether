<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Hints\HintContext;
use App\Data\Occupants\DateRange;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Compatibility\CompatibilityCalculatorService;
use App\Services\Hints\Concerns\BuildsGuestHints;
use Illuminate\Support\Collection;

class CompatibilityHintService
{
    use BuildsGuestHints;

    public function __construct(private readonly CompatibilityCalculatorService $calculator) {}

    public function doesNotFitSelectedCriteria(User $guest, SleepingPlace $place, HintContext $context): ?GuestHintData
    {
        $guest->loadMissing('guestCompatibilityProfile');
        $profile = $guest->guestCompatibilityProfile;

        if (! $profile) {
            return null;
        }

        if (($profile->avoids_upper_bunk && $place->is_top_bunk) || ($profile->needs_locker && ! $place->has_locker)) {
            return $this->hint('criteria_mismatch', 'compatibility', 'compatibility', 'high', 89, beforeBooking: true, dismissible: false, source: 'compatibility');
        }

        return null;
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getCompatibilityWarnings(User $guest, SleepingPlace $place, HintContext $context): Collection
    {
        $range = $this->range($context);

        if (! $range) {
            return collect();
        }

        $result = $this->calculator->calculate($guest, $place, $range);

        if ($result->blockingReasons !== [] || $result->warningReasons !== []) {
            return collect([
                $this->hint('compatibility_warnings', 'compatibility', 'warning', 'high', 77, beforeBooking: true, source: 'compatibility'),
            ]);
        }

        return collect();
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getCompatibilityPositiveHints(User $guest, SleepingPlace $place, HintContext $context): Collection
    {
        $range = $this->range($context);

        if (! $range) {
            return collect();
        }

        $result = $this->calculator->calculate($guest, $place, $range);

        if (in_array($result->fitStatus, ['great', 'good'], true)) {
            return collect([
                $this->hint('compatibility_good_fit', 'compatibility', 'positive', 'medium', 60, card: true, source: 'compatibility'),
            ]);
        }

        return collect();
    }

    private function range(HintContext $context): ?DateRange
    {
        $range = $context->dateRange();

        return $range?->valid() ? $range : null;
    }
}
