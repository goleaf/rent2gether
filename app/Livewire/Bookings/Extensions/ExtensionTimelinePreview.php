<?php

namespace App\Livewire\Bookings\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\BookingExtension;
use App\Services\Bookings\BookingExtensionTimelineService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ExtensionTimelinePreview extends Component
{
    use LoadsBookingExtension;

    public function mount(BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension(extension: $extension);
    }

    public function render(): View
    {
        $data = $this->extensionViewData('timeline');
        $data['timeline'] = $data['extension']
            ? app(BookingExtensionTimelineService::class)->buildTimelineDates($data['extension'])
            : collect();

        return view('livewire.bookings.extensions.card', $data);
    }
}
