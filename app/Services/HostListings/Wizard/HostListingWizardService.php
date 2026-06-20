<?php

namespace App\Services\HostListings\Wizard;

use App\Models\HostListingWizardSession;
use App\Models\Property;
use App\Models\User;

class HostListingWizardService
{
    private const STEPS = ['property', 'rooms', 'sleeping_places', 'calendar', 'publish'];

    public function start(User $host): HostListingWizardSession
    {
        $property = app(HostPropertyDraftService::class)->createOrUpdateProperty($host, [
            'title' => __('listing_wizard.defaults.property_title'),
            'description' => '',
            'publication_status' => 'draft',
        ]);

        return HostListingWizardSession::query()->create([
            'user_id' => $host->id,
            'property_id' => $property->id,
            'current_step' => 'property',
            'completed_steps_json' => [],
            'skipped_steps_json' => [],
            'last_saved_at' => now(),
            'status' => 'draft',
        ]);
    }

    public function resume(User $host, Property $property): HostListingWizardSession
    {
        $this->authorizeHost($host, $property);

        return HostListingWizardSession::query()->firstOrCreate(
            [
                'user_id' => $host->id,
                'property_id' => $property->id,
            ],
            [
                'current_step' => 'property',
                'completed_steps_json' => [],
                'skipped_steps_json' => [],
                'last_saved_at' => now(),
                'status' => 'draft',
            ],
        );
    }

    public function saveStep(User $host, Property $property, string $step, array $data): void
    {
        $this->authorizeHost($host, $property);

        $session = $this->resume($host, $property);
        $session->forceFill([
            'current_step' => $data['next_step'] ?? $step,
            'last_saved_at' => now(),
        ])->save();
    }

    public function markStepCompleted(Property $property, string $step): void
    {
        $session = $property->listingWizardSessions()->latest('id')->first();

        if (! $session instanceof HostListingWizardSession) {
            return;
        }

        $completed = collect($session->completed_steps_json ?? [])
            ->push($step)
            ->unique()
            ->values()
            ->all();

        $session->forceFill([
            'completed_steps_json' => $completed,
            'last_saved_at' => now(),
        ])->save();
    }

    public function getCurrentStep(Property $property): string
    {
        return $property->listingWizardSessions()->latest('id')->value('current_step') ?: 'property';
    }

    /**
     * @return array{current:string, completed:list<string>, percentage:int, steps:list<string>}
     */
    public function getProgress(Property $property): array
    {
        $session = $property->listingWizardSessions()->latest('id')->first();
        $completed = $session instanceof HostListingWizardSession ? ($session->completed_steps_json ?? []) : [];

        return [
            'current' => $session?->current_step ?? 'property',
            'completed' => array_values($completed),
            'percentage' => (int) round((count($completed) / count(self::STEPS)) * 100),
            'steps' => self::STEPS,
        ];
    }

    private function authorizeHost(User $host, Property $property): void
    {
        abort_unless($property->isOwnedBy($host), 403);
    }
}
