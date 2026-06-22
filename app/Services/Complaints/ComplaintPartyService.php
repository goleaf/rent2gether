<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\ComplaintParty;
use Illuminate\Support\Collection;

class ComplaintPartyService
{
    /**
     * @return Collection<int, ComplaintParty>
     */
    public function createParties(ComplaintCase $case): Collection
    {
        $case->loadMissing('reporter', 'against', 'guest', 'host');
        $parties = collect();

        if ($case->reporter_user_id) {
            $parties->push(ComplaintParty::query()->create([
                'complaint_case_id' => $case->id,
                'user_id' => $case->reporter_user_id,
                'party_type' => 'reporter',
                'display_name_snapshot' => $case->reporter?->name,
                'role_in_case' => 'reported_problem',
                'can_respond' => true,
            ]));
        }

        $againstUserId = $case->against_user_id ?: ($case->submitted_by_type === 'guest' ? $case->host_user_id : $case->guest_user_id);

        if ($againstUserId) {
            $parties->push(ComplaintParty::query()->create([
                'complaint_case_id' => $case->id,
                'user_id' => $againstUserId,
                'party_type' => 'against',
                'display_name_snapshot' => $case->against?->name ?: ($case->submitted_by_type === 'guest' ? $case->host?->name : $case->guest?->name),
                'role_in_case' => 'accused',
                'can_respond' => true,
                'notified_at' => $case->other_party_notified_at,
            ]));
        }

        return $parties;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addWitness(ComplaintCase $case, array $data): ComplaintParty
    {
        return ComplaintParty::query()->create([
            'complaint_case_id' => $case->id,
            'user_id' => $data['user_id'] ?? null,
            'party_type' => 'witness',
            'display_name_snapshot' => $data['display_name_snapshot'] ?? null,
            'role_in_case' => 'witness',
            'can_respond' => (bool) ($data['can_respond'] ?? false),
        ]);
    }

    public function notifyOtherParty(ComplaintCase $case): void
    {
        $case->parties()
            ->where('party_type', 'against')
            ->update(['notified_at' => now()]);
    }

    public function markResponded(ComplaintParty $party): ComplaintParty
    {
        $party->forceFill(['responded_at' => now()])->save();

        return $party->fresh();
    }
}
