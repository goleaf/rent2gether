<?php

namespace App\Livewire\Host\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\SleepingPlaceCalendarBlockService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostCalendarBlockSheet extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $startsAt = '';

    public string $endsAt = '';

    public string $blockType = 'closed_by_host';

    public ?string $note = null;

    public function mount(int $sleepingPlaceId, ?string $startsAt = null, ?string $endsAt = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->startsAt = $startsAt ?? now()->toDateString();
        $this->endsAt = $endsAt ?? now()->addDay()->toDateString();
    }

    public function create(SleepingPlaceCalendarBlockService $blocks): void
    {
        $validated = $this->validate([
            'startsAt' => ['required', 'date'],
            'endsAt' => ['required', 'date', 'after:startsAt'],
            'blockType' => ['required', Rule::in(['closed_by_host', 'cleaning', 'repair', 'request_only', 'hidden'])],
            'note' => ['nullable', 'string', 'max:500'],
        ], attributes: $this->validationAttributes());

        $place = SleepingPlace::query()->findOrFail($this->sleepingPlaceId);

        $blocks->createBlock(auth()->user(), $place, [
            'starts_at' => CarbonImmutable::parse($validated['startsAt']),
            'ends_at' => CarbonImmutable::parse($validated['endsAt']),
            'block_type' => $validated['blockType'],
            'reason_key' => $validated['blockType'],
            'note' => $validated['note'] ?? null,
        ]);

        $this->dispatch('availability-block-created');
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-calendar-block-sheet');
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('calendar.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
