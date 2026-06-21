<?php

namespace App\Livewire\Bookings\Requests;

use App\Models\BookingRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingRequestWarnings extends Component
{
    #[Locked]
    public int $requestId;

    public string $audience = 'guest';

    public function mount(int|BookingRequest $request, string $audience = 'guest'): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
        $this->audience = $audience;
    }

    public function render(): View
    {
        $request = BookingRequest::query()
            ->with(['warnings' => fn ($query) => $query->orderByDesc('blocking')->orderBy('id')])
            ->findOrFail($this->requestId);

        $warnings = $request->warnings
            ->filter(fn ($warning): bool => $this->audience === 'host' ? (bool) $warning->visible_to_host : (bool) $warning->visible_to_guest)
            ->map(fn ($warning): array => [
                'key' => $warning->warning_key,
                'severity' => $warning->severity,
                'message' => __($warning->message_key, $warning->message_params_json ?? []),
            ])
            ->values()
            ->all();

        return view('livewire.bookings.requests.booking-request-warnings', [
            'warnings' => $warnings,
        ]);
    }
}
