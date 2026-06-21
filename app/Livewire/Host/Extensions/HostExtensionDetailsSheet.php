<?php

namespace App\Livewire\Host\Extensions;

use App\Livewire\Bookings\Extensions\Concerns\LoadsBookingExtension;
use App\Models\BookingExtension;
use App\Services\Bookings\BookingExtensionHostResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HostExtensionDetailsSheet extends Component
{
    use LoadsBookingExtension;

    public string $responseMessage = '';

    public string $rejectionReason = '';

    public string $proposedNewCheckOutDate = '';

    public function mount(BookingExtension|int|null $extension = null): void
    {
        $this->mountBookingExtension(extension: $extension);
    }

    public function approve(): void
    {
        $extension = $this->extension();

        if ($extension && Auth::user()) {
            app(BookingExtensionHostResponseService::class)->approve(Auth::user(), $extension, $this->responseMessage ?: null);
        }
    }

    public function reject(): void
    {
        $extension = $this->extension();

        if ($extension && Auth::user()) {
            app(BookingExtensionHostResponseService::class)->reject(
                Auth::user(),
                $extension,
                $this->rejectionReason ?: __('booking_extensions.messages.host_rejected')
            );
        }
    }

    public function proposeNewCheckout(): void
    {
        $extension = $this->extension();

        if ($extension && Auth::user() && $this->proposedNewCheckOutDate !== '') {
            app(BookingExtensionHostResponseService::class)->proposeNewCheckout(Auth::user(), $extension, [
                'proposed_new_check_out_date' => $this->proposedNewCheckOutDate,
                'message' => $this->responseMessage ?: null,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.host.extensions.card', $this->extensionViewData('details'));
    }
}
