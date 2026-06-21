<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostRequestWarningsPanel extends Component
{
    #[Locked]
    public int $requestId;

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function render(): View
    {
        $request = BookingRequest::query()
            ->with(['warnings' => fn ($query) => $query->where('visible_to_host', true)->orderByDesc('blocking')->orderBy('id')])
            ->findOrFail($this->requestId);

        return view('livewire.host.booking-requests.host-request-warnings-panel', [
            'warnings' => $request->warnings
                ->map(fn ($warning): array => [
                    'severity' => $warning->severity,
                    'message' => __($warning->message_key, $warning->message_params_json ?? []),
                ])
                ->values()
                ->all(),
        ]);
    }
}
