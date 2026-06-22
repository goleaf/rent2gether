<?php

namespace App\Livewire\Host\Reviews;

use App\Models\HostReputationSnapshot;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostReputationSummary extends Component
{
    public ?int $hostUserId = null;

    #[Computed]
    public function snapshot(): ?HostReputationSnapshot
    {
        if ($this->hostUserId === null) {
            return null;
        }

        return HostReputationSnapshot::query()
            ->select(['id', 'host_user_id', 'overall_rating', 'reviews_count', 'verified_host'])
            ->where('host_user_id', $this->hostUserId)
            ->first();
    }

    public function render(): View
    {
        return view('livewire.host.reviews.host-reputation-summary', [
            'snapshot' => $this->snapshot,
        ]);
    }
}
