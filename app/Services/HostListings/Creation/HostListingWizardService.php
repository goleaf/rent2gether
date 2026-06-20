<?php

namespace App\Services\HostListings\Creation;

use App\Models\ListingCreationDraft;
use App\Models\User;

class HostListingWizardService
{
    /** @var list<string> */
    private array $steps = ['property', 'rooms', 'sleeping_places', 'photos', 'rules', 'readiness'];

    public function __construct(private readonly ListingDraftService $drafts) {}

    public function start(User $host): ListingCreationDraft
    {
        return $this->drafts->createDraft($host, 'full_listing_wizard');
    }

    public function getCurrentStep(User $host, string $draftId): string
    {
        $draft = ListingCreationDraft::query()->whereKey($draftId)->where('user_id', $host->id)->firstOrFail();

        return $draft->current_step;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveStep(User $host, ListingCreationDraft $draft, array $data): ListingCreationDraft
    {
        return $this->drafts->saveDraft($host, $draft, $data);
    }

    public function goToNextStep(User $host, ListingCreationDraft $draft): ListingCreationDraft
    {
        $index = array_search($draft->current_step, $this->steps, true);
        $next = $this->steps[min(($index === false ? 0 : $index) + 1, count($this->steps) - 1)];

        return $this->drafts->saveDraft($host, $draft, ['current_step' => $next]);
    }

    public function goToPreviousStep(User $host, ListingCreationDraft $draft): ListingCreationDraft
    {
        $index = array_search($draft->current_step, $this->steps, true);
        $previous = $this->steps[max(($index === false ? 0 : $index) - 1, 0)];

        return $this->drafts->saveDraft($host, $draft, ['current_step' => $previous]);
    }

    /**
     * @return array<string, mixed>
     */
    public function finish(User $host, ListingCreationDraft $draft): array
    {
        $draft = $this->drafts->saveDraft($host, $draft, ['current_step' => 'readiness']);

        return [
            'draft_id' => $draft->id,
            'current_step' => $draft->current_step,
            'completed_steps' => $draft->completed_steps_json ?? [],
        ];
    }
}
