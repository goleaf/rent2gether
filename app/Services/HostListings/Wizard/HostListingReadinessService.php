<?php

namespace App\Services\HostListings\Wizard;

use App\Models\ListingPublicationCheck;
use App\Models\Property;
use Illuminate\Support\Collection;

class HostListingReadinessService
{
    public function checkProperty(Property $property): array
    {
        return $this->checks($property)->where('category', 'property')->values()->all();
    }

    public function checkRooms(Property $property): array
    {
        return $this->checks($property)->where('category', 'rooms')->values()->all();
    }

    public function checkSleepingPlaces(Property $property): array
    {
        return $this->checks($property)->where('category', 'sleeping_places')->values()->all();
    }

    public function checkCalendar(Property $property): array
    {
        return $this->checks($property)->where('category', 'calendar')->values()->all();
    }

    /**
     * @return array{ready:bool, score:int, blocking:list<array<string,mixed>>, recommended:list<array<string,mixed>>, checks:list<array<string,mixed>>}
     */
    public function checkPublishReadiness(Property $property): array
    {
        $checks = $this->checks($property);
        $this->persistChecks($property, $checks);
        $blocking = $checks->where('is_blocking', true)->where('status', 'open')->values();
        $recommended = $checks->where('is_blocking', false)->where('status', 'open')->values();

        return [
            'ready' => $blocking->isEmpty(),
            'score' => $this->calculateCompletionScore($property),
            'blocking' => $blocking->all(),
            'recommended' => $recommended->all(),
            'checks' => $checks->values()->all(),
        ];
    }

    public function getBlockingIssues(Property $property): array
    {
        return $this->checkPublishReadiness($property)['blocking'];
    }

    public function getRecommendedImprovements(Property $property): array
    {
        return $this->checkPublishReadiness($property)['recommended'];
    }

    public function calculateCompletionScore(Property $property): int
    {
        $checks = $this->checks($property);
        $fixed = $checks->where('status', 'fixed')->count();

        return $checks->isEmpty() ? 0 : (int) round(($fixed / $checks->count()) * 100);
    }

    private function checks(Property $property): Collection
    {
        $property->loadMissing([
            'rooms.sleepingPlaces.availabilityDays',
            'rooms.sleepingPlaces.calendarSettings',
            'rooms.sleepingPlaces.calendarDays',
            'rooms.sleepingPlaces.mediaItems',
            'mediaItems',
            'accessDetails',
            'host.hostProfile',
        ]);
        $rooms = $property->rooms;
        $places = $rooms->flatMap->sleepingPlaces;

        return collect([
            $this->check('missing_rooms', 'rooms', $rooms->isNotEmpty(), true, true),
            $this->check('missing_sleeping_places', 'sleeping_places', $places->isNotEmpty(), true, true),
            $this->check('missing_price', 'sleeping_places', $places->isNotEmpty() && $places->every(fn ($place): bool => (float) $place->base_price_per_night > 0), true, true),
            $this->check('missing_calendar_settings', 'calendar', $places->isNotEmpty() && $places->every(fn ($place): bool => $place->calendarSettings !== null), true, true),
            $this->check('missing_available_dates', 'calendar', $places->isNotEmpty() && $places->every(fn ($place): bool => $place->calendarDays->contains(fn ($day): bool => $day->status === 'available')), true, true),
            $this->check('missing_photos', 'photos', $property->mediaItems->isNotEmpty() && $places->every(fn ($place): bool => $place->mediaItems->isNotEmpty()), true, true),
            $this->check('missing_house_rules', 'rules', filled($property->rules) || $property->rules()->exists() || $property->translations()->whereNotNull('house_rules_text')->exists(), true, true),
            $this->check('missing_check_in_time', 'access', filled($property->host?->hostProfile?->default_check_in_time) || $places->contains(fn ($place): bool => filled($place->calendarSettings?->check_in_time_from)), true, true),
            $this->check('missing_check_out_time', 'access', filled($property->host?->hostProfile?->default_check_out_time) || $places->contains(fn ($place): bool => filled($place->calendarSettings?->check_out_time_until)), true, true),
            $this->check('missing_key_pickup_method', 'access', filled($property->accessDetails?->key_pickup_method) || filled($property->access_instructions), true, true),
            $this->check('missing_deposit', 'pricing', $places->isNotEmpty() && $places->every(fn ($place): bool => $place->deposit_amount !== null), true, true),
            $this->check('missing_cancellation_policy', 'pricing', $places->isNotEmpty() && $places->every(fn ($place): bool => filled($place->cancellation_policy) || filled($property->host?->hostProfile?->default_cancellation_policy)), true, true),
            $this->check('missing_kitchen_rules', 'rules', $this->hasRuleText($property, 'kitchen'), true, true),
            $this->check('missing_bathroom_rules', 'rules', $this->hasRuleText($property, 'bathroom'), true, true),
            $this->check('missing_emergency_contact', 'safety', filled($property->emergency_contact_name) || filled($property->emergency_contact_phone) || $property->accessDetails?->emergency_contact_available === true, true, true),
            $this->check('add_more_photos', 'photos', $places->sum(fn ($place): int => $place->mediaItems->count()) >= max(1, $places->count() * 2)),
            $this->check('add_bathroom_photos', 'photos', $property->mediaItems->contains(fn ($media): bool => $media->collection === 'bathroom')),
            $this->check('add_kitchen_photos', 'photos', $property->mediaItems->contains(fn ($media): bool => $media->collection === 'kitchen')),
            $this->check('add_locker_info', 'sleeping_places', $places->isNotEmpty() && $places->every(fn ($place): bool => $place->has_locker !== null)),
            $this->check('missing_quiet_hours', 'rules', $this->hasRuleText($property, 'quiet')),
            $this->check('missing_weekly_discount', 'pricing', $places->contains(fn ($place): bool => filled($place->weekly_price))),
            $this->check('missing_monthly_discount', 'pricing', $places->contains(fn ($place): bool => filled($place->monthly_price))),
        ])->values();
    }

    private function check(string $key, string $category, bool $fixed, bool $required = false, bool $blocking = false): array
    {
        return [
            'check_key' => $key,
            'category' => $category,
            'severity' => $blocking ? 'critical' : 'recommended',
            'status' => $fixed ? 'fixed' : 'open',
            'message_key' => 'listing_wizard.checks.'.$key,
            'message_params_json' => [],
            'is_required' => $required,
            'is_blocking' => $blocking && ! $fixed,
            'fixed_at' => $fixed ? now() : null,
        ];
    }

    private function persistChecks(Property $property, Collection $checks): void
    {
        ListingPublicationCheck::query()->where('property_id', $property->id)->delete();

        $checks->each(function (array $check) use ($property): void {
            ListingPublicationCheck::query()->create(array_merge($check, [
                'user_id' => $property->host_user_id,
                'property_id' => $property->id,
            ]));
        });
    }

    private function hasRuleText(Property $property, string $needle): bool
    {
        return str_contains((string) json_encode($property->rules ?? []), $needle)
            || $property->translations()->where('house_rules_text', 'like', '%'.$needle.'%')->exists()
            || $property->rules()->where(function ($query) use ($needle): void {
                $query->where('slug', 'like', '%'.$needle.'%')
                    ->orWhere('category', 'like', '%'.$needle.'%');
            })->exists();
    }
}
