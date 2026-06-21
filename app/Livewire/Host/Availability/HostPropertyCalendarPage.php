<?php

namespace App\Livewire\Host\Availability;

use App\Models\Property;
use App\Services\HostCalendar\HostCalendarViewService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostPropertyCalendarPage extends Component
{
    #[Locked]
    public int $propertyId;

    public string $from = '';

    public string $to = '';

    public function mount(int $propertyId, ?string $from = null, ?string $to = null): void
    {
        $this->propertyId = $propertyId;
        $this->from = $from ?? now()->toDateString();
        $this->to = $to ?? now()->addDays(14)->toDateString();
    }

    #[Computed]
    public function cards(): array
    {
        $property = Property::query()
            ->with(['sleepingPlaces:id,room_id,property_id,user_id,title,display_name,status'])
            ->find($this->propertyId);

        if (! $property instanceof Property) {
            return [];
        }

        $view = app(HostCalendarViewService::class);
        $from = CarbonImmutable::parse($this->from);
        $to = CarbonImmutable::parse($this->to);

        return $property->sleepingPlaces
            ->flatMap(fn ($place) => $view->sleepingPlaceCards($place, $from, $to))
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.host.availability.host-property-calendar-page');
    }
}
