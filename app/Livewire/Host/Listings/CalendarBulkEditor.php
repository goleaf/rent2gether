<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CalendarBulkEditor extends Component
{
    #[Locked]
    public int $propertyId;

    public string $start = '';

    public string $end = '';

    public ?float $price = null;

    public ?int $minNights = null;

    public ?int $maxNights = null;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
        $this->start = now()->addDay()->toDateString();
        $this->end = now()->addDays(8)->toDateString();
    }

    public function openDates(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $property = Property::query()->findOrFail($this->propertyId);

        abort_unless($host instanceof User, 403);

        $calendar->bulkOpenDates($host, $property, ['start' => $this->start, 'end' => $this->end], [
            'price' => $this->price,
            'min_nights' => $this->minNights,
            'max_nights' => $this->maxNights,
        ]);
        $this->dispatch('listing-step-saved');
    }

    public function closeDates(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $property = Property::query()->findOrFail($this->propertyId);

        abort_unless($host instanceof User, 403);

        $calendar->bulkCloseDates($host, $property, ['start' => $this->start, 'end' => $this->end], 'host_blocked');
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.calendar-bulk-editor');
    }
}
