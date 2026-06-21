<?php

namespace App\Livewire\Host\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\SleepingPlaceCalendarDayService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostCalendarDayEditor extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $date = '';

    public string $status = 'available';

    public ?string $note = null;

    public function mount(int $sleepingPlaceId, ?string $date = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->date = $date ?? now()->toDateString();
    }

    public function save(SleepingPlaceCalendarDayService $days): void
    {
        $validated = $this->validate([
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['available', 'closed_by_host', 'repair', 'cleaning', 'request_only', 'temporarily_hidden'])],
            'note' => ['nullable', 'string', 'max:500'],
        ], attributes: $this->validationAttributes());

        $place = SleepingPlace::query()->findOrFail($this->sleepingPlaceId);
        $days->setDayStatus(auth()->user(), $place, CarbonImmutable::parse($validated['date']), $validated['status'], [
            'note' => $validated['note'] ?? null,
        ]);

        $this->dispatch('availability-day-saved');
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-calendar-day-editor');
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
