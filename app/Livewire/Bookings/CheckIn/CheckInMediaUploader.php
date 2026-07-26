<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInMediaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CheckInMediaUploader extends Component
{
    use LoadsBookingCheckIn;
    use WithFileUploads;

    public string $mediaRole = 'before_check_in_sleeping_place';

    public string $caption = '';

    /**
     * @var TemporaryUploadedFile|null
     */
    public $photo = null;

    public function record(): void
    {
        $checkIn = $this->checkIn();
        $user = Auth::user();

        if ($checkIn && $user) {
            $validated = $this->validate([
                'mediaRole' => ['required', 'string', 'in:'.implode(',', array_keys(__('check_in.media_roles')))],
                'photo' => ['required', 'image', 'max:2048'],
                'caption' => ['nullable', 'string', 'max:500'],
            ], [], __('check_in.validation.attributes'));
            /** @var TemporaryUploadedFile $photo */
            $photo = $validated['photo'];
            $path = $photo->store("check-ins/{$checkIn->id}", 'public');

            app(BookingCheckInMediaService::class)->recordMedia($user, $checkIn, [
                'media_type' => 'photo',
                'media_role' => $validated['mediaRole'],
                'path' => $path,
                'caption' => $validated['caption'] ?? null,
                'visibility' => 'guest_and_host',
            ]);
            $this->reset('photo', 'caption');
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('media_uploader'));
    }
}
