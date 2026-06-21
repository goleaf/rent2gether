<?php

namespace App\Livewire\Bookings\Dates;

use App\Models\BookingQuote;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AvailabilityWarnings extends Component
{
    #[Locked]
    public int $quoteId;

    public function mount(int|BookingQuote $quoteId): void
    {
        $this->quoteId = $quoteId instanceof BookingQuote ? $quoteId->id : $quoteId;
    }

    public function render(): View
    {
        $quote = BookingQuote::query()
            ->with(['validationResults' => fn ($query) => $query->where('visible_to_guest', true)->orderByDesc('blocking')->orderBy('id')])
            ->findOrFail($this->quoteId);

        return view('livewire.bookings.dates.availability-warnings', [
            'availabilityStatus' => $quote->availability_status,
            'messages' => $quote->validationResults
                ->map(fn ($result): array => [
                    'key' => $result->validation_key,
                    'message' => __($result->message_key, $result->message_params_json ?? []),
                    'blocking' => (bool) $result->blocking,
                    'severity' => $result->severity,
                ])
                ->values()
                ->all(),
        ]);
    }
}
