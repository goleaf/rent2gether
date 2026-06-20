<?php

namespace App\Services\HostListings\Creation;

use App\Models\ListingCreationDraft;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ListingDraftService
{
    public function createDraft(User $host, string $type): ListingCreationDraft
    {
        return ListingCreationDraft::query()->create([
            'user_id' => $host->id,
            'draft_type' => $type,
            'current_step' => 'property',
            'draft_data_json' => [],
            'completed_steps_json' => [],
            'last_saved_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function saveDraft(User $host, ListingCreationDraft $draft, array $data): ListingCreationDraft
    {
        $this->authorize($host, $draft);
        $draft->update([
            'property_id' => $data['property_id'] ?? $draft->property_id,
            'room_id' => $data['room_id'] ?? $draft->room_id,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? $draft->sleeping_place_id,
            'current_step' => $data['current_step'] ?? $draft->current_step,
            'draft_data_json' => $data['draft_data_json'] ?? $draft->draft_data_json ?? [],
            'completed_steps_json' => $data['completed_steps_json'] ?? $draft->completed_steps_json ?? [],
            'last_saved_at' => now(),
        ]);

        return $draft->refresh();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws AuthorizationException
     */
    public function restoreDraft(User $host, ListingCreationDraft $draft): array
    {
        $this->authorize($host, $draft);

        return $draft->draft_data_json ?? [];
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteDraft(User $host, ListingCreationDraft $draft): void
    {
        $this->authorize($host, $draft);
        $draft->delete();
    }

    private function authorize(User $host, ListingCreationDraft $draft): void
    {
        if ((int) $draft->user_id !== (int) $host->id) {
            throw new AuthorizationException(__('domain.errors.not_property_owner'));
        }
    }
}
