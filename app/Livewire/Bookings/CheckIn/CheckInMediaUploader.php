<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInMediaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CheckInMediaUploader extends Component
{
    use LoadsBookingCheckIn;

    public string $mediaRole = 'before_check_in_sleeping_place';

    public string $path = '';

    public string $caption = '';

    public function record(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user() && $this->path !== '') {
            app(BookingCheckInMediaService::class)->recordMedia(Auth::user(), $checkIn, [
                'media_type' => 'photo',
                'media_role' => $this->mediaRole,
                'path' => $this->path,
                'caption' => $this->caption,
                'visibility' => 'guest_and_host',
            ]);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('media_uploader'));
    }
}
