<?php

namespace App\Services\HostHints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;

class HostListingQualityService
{
    public function calculateCompletionScore(Property|Room|SleepingPlace $target): int
    {
        $checks = $this->checks($target);
        $done = collect($checks)->where('complete', true)->count();

        return count($checks) > 0 ? (int) round(($done / count($checks)) * 100) : 0;
    }

    /**
     * @return list<string>
     */
    public function getMissingRequiredFields(Property|Room|SleepingPlace $target): array
    {
        return collect($this->checks($target))
            ->where('required', true)
            ->where('complete', false)
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function getMissingRecommendedFields(Property|Room|SleepingPlace $target): array
    {
        return collect($this->checks($target))
            ->where('required', false)
            ->where('complete', false)
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function getCriticalIssues(Property|Room|SleepingPlace $target): array
    {
        return collect($this->checks($target))
            ->where('critical', true)
            ->where('complete', false)
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * @return array{ready:bool, score:int, critical:list<string>, required:list<string>, recommended:list<string>}
     */
    public function getPublishReadiness(Property|Room|SleepingPlace $target): array
    {
        $critical = $this->getCriticalIssues($target);

        return [
            'ready' => $critical === [],
            'score' => $this->calculateCompletionScore($target),
            'critical' => $critical,
            'required' => $this->getMissingRequiredFields($target),
            'recommended' => $this->getMissingRecommendedFields($target),
        ];
    }

    /**
     * @return list<array{key:string,label_key:string,complete:bool,required:bool,critical:bool}>
     */
    private function checks(Property|Room|SleepingPlace $target): array
    {
        if ($target instanceof SleepingPlace) {
            $target->loadMissing(['translations', 'mediaItems', 'availabilityDays', 'property.host.hostProfile', 'room']);

            return [
                $this->check('title', $target->translations->contains(fn ($translation): bool => filled($translation->title)) || filled($target->display_name), true),
                $this->check('photos', $target->mediaItems->where('status', 'active')->isNotEmpty(), true, true),
                $this->check('price', (float) $target->base_price_per_night > 0, true, true),
                $this->check('calendar', $target->availabilityDays->isNotEmpty(), true, true),
                $this->check('access', filled($target->property?->host?->hostProfile?->default_check_in_time) && filled($target->property?->host?->hostProfile?->default_check_out_time), true),
                $this->check('rules', filled($target->room?->room_rules_text) || $target->property?->rules()->exists() === true, true),
                $this->check('deposit', (float) $target->deposit_amount > 0),
                $this->check('cleaning_fee', (float) $target->cleaning_fee > 0),
                $this->check('weekly_discount', filled($target->weekly_price)),
                $this->check('monthly_discount', filled($target->monthly_price)),
                $this->check('cancellation_policy', filled($target->cancellation_policy) || filled($target->property?->host?->hostProfile?->default_cancellation_policy)),
                $this->check('locker', (bool) $target->has_locker),
            ];
        }

        if ($target instanceof Room) {
            return [
                $this->check('title', filled($target->title), true),
                $this->check('type', filled($target->type), true),
                $this->check('rules', filled($target->room_rules_text), true),
                $this->check('photos', $target->mediaItems()->active()->exists()),
            ];
        }

        return [
            $this->check('title', filled($target->title), true),
            $this->check('description', filled($target->description), true),
            $this->check('address', filled($target->address_line_1 ?: $target->street), true),
            $this->check('photos', $target->mediaItems()->active()->exists(), true),
            $this->check('emergency_contact', filled($target->emergency_contact_name) || filled($target->emergency_contact_phone)),
        ];
    }

    /**
     * @return array{key:string,label_key:string,complete:bool,required:bool,critical:bool}
     */
    private function check(string $key, bool $complete, bool $required = false, bool $critical = false): array
    {
        return [
            'key' => $key,
            'label_key' => 'host_hints.quality.items.'.$key,
            'complete' => $complete,
            'required' => $required,
            'critical' => $critical,
        ];
    }
}
