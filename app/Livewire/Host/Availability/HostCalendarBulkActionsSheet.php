<?php

namespace App\Livewire\Host\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\CalendarBulkActionService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostCalendarBulkActionsSheet extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $from = '';

    public string $to = '';

    public function mount(int $sleepingPlaceId, ?string $from = null, ?string $to = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->from = $from ?? now()->toDateString();
        $this->to = $to ?? now()->addDays(7)->toDateString();
    }

    public function open(CalendarBulkActionService $bulk): void
    {
        $bulk->bulkOpenDates(auth()->user(), $this->place(), $this->fromDate(), $this->toDate());
        $this->dispatch('availability-bulk-updated');
    }

    public function close(CalendarBulkActionService $bulk): void
    {
        $bulk->bulkCloseDates(auth()->user(), $this->place(), $this->fromDate(), $this->toDate(), 'closed_by_host');
        $this->dispatch('availability-bulk-updated');
    }

    public function requestOnly(CalendarBulkActionService $bulk): void
    {
        $bulk->bulkSetRequestOnly(auth()->user(), $this->place(), $this->fromDate(), $this->toDate());
        $this->dispatch('availability-bulk-updated');
    }

    public function markRepair(CalendarBulkActionService $bulk): void
    {
        $bulk->bulkMarkRepair(auth()->user(), $this->place(), $this->fromDate(), $this->toDate());
        $this->dispatch('availability-bulk-updated');
    }

    public function markCleaning(CalendarBulkActionService $bulk): void
    {
        $bulk->bulkMarkCleaning(auth()->user(), $this->place(), $this->fromDate(), $this->toDate());
        $this->dispatch('availability-bulk-updated');
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-calendar-bulk-actions-sheet');
    }

    private function place(): SleepingPlace
    {
        return SleepingPlace::query()->findOrFail($this->sleepingPlaceId);
    }

    private function fromDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->from);
    }

    private function toDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->to);
    }
}
