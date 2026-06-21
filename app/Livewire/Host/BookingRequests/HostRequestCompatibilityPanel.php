<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostRequestCompatibilityPanel extends Component
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
            ->with(['compatibilityResults' => fn ($query) => $query->orderBy('id')])
            ->findOrFail($this->requestId);

        return view('livewire.host.booking-requests.host-request-compatibility-panel', [
            'results' => $request->compatibilityResults
                ->map(fn ($result): array => [
                    'status' => $result->status,
                    'severity' => $result->severity,
                    'message' => __($result->message_key, $result->message_params_json ?? []),
                ])
                ->values()
                ->all(),
        ]);
    }
}
