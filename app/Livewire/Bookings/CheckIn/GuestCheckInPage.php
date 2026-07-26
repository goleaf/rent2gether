<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInMediaService;
use App\Services\CheckIn\BookingCheckInProblemService;
use App\Services\CheckIn\BookingCheckInService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class GuestCheckInPage extends Component
{
    use LoadsBookingCheckIn;
    use WithFileUploads;

    public string $actualArrivalTime = '';

    public string $problemType = 'other';

    public string $severity = 'medium';

    public string $description = '';

    public string $mediaCaption = '';

    /**
     * @var TemporaryUploadedFile|null
     */
    public $beforePhoto = null;

    /**
     * @var TemporaryUploadedFile|null
     */
    public $problemPhoto = null;

    public function markOnTheWay(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInService::class)->markGuestOnTheWay(Auth::user(), $checkIn);
            $this->refreshCheckInState();
        }
    }

    public function markArrived(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            $validated = $this->validate([
                'actualArrivalTime' => ['nullable', 'date_format:H:i'],
            ], [], $this->validationAttributes());

            app(BookingCheckInService::class)->markGuestArrived(
                Auth::user(),
                $checkIn,
                $this->arrivalDateTime($checkIn, $validated['actualArrivalTime'] ?? null),
            );
            $this->refreshCheckInState();
        }
    }

    public function saveBeforePhoto(): void
    {
        $checkIn = $this->checkIn();
        $user = Auth::user();

        if (! $checkIn || ! $user) {
            return;
        }

        $validated = $this->validate([
            'beforePhoto' => ['required', 'image', 'max:2048'],
            'mediaCaption' => ['nullable', 'string', 'max:500'],
        ], [], $this->validationAttributes());

        /** @var TemporaryUploadedFile $photo */
        $photo = $validated['beforePhoto'];
        $path = $photo->store("check-ins/{$checkIn->id}", 'public');

        app(BookingCheckInMediaService::class)->recordMedia($user, $checkIn, [
            'media_type' => 'photo',
            'media_role' => 'before_check_in_sleeping_place',
            'path' => $path,
            'caption' => $validated['mediaCaption'] ?? null,
            'visibility' => 'guest_and_host',
        ]);

        $this->reset('beforePhoto', 'mediaCaption');
        $this->refreshCheckInState();
    }

    public function reportProblem(): void
    {
        $checkIn = $this->checkIn();
        $user = Auth::user();

        if (! $checkIn || ! $user) {
            return;
        }

        $validated = $this->validate([
            'problemType' => ['required', 'string', 'in:'.implode(',', array_keys(__('check_in.problems')))],
            'severity' => ['required', 'string', 'in:'.implode(',', array_keys(__('check_in.severities')))],
            'description' => ['nullable', 'string', 'max:2000'],
            'problemPhoto' => ['nullable', 'image', 'max:2048'],
        ], [], $this->validationAttributes());

        $photoPaths = [];

        $problemPhoto = $validated['problemPhoto'] ?? null;

        if ($problemPhoto instanceof TemporaryUploadedFile) {
            $path = $problemPhoto->store("check-ins/{$checkIn->id}/problems", 'public');
            $photoPaths[] = $path;

            app(BookingCheckInMediaService::class)->recordMedia($user, $checkIn, [
                'media_type' => 'photo',
                'media_role' => 'problem_evidence',
                'path' => $path,
                'caption' => $validated['description'] ?? null,
                'visibility' => 'guest_and_host',
            ]);
        }

        app(BookingCheckInProblemService::class)->reportProblem($user, $checkIn->refresh(), [
            'problem_type' => $validated['problemType'],
            'severity' => $validated['severity'],
            'description' => $validated['description'] ?? null,
            'photo_paths' => $photoPaths,
            'guest_wants_help' => true,
        ]);

        $this->reset('problemPhoto', 'description');
        $this->refreshCheckInState();
    }

    public function confirm(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInService::class)->confirmByGuest(Auth::user(), $checkIn);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('guest_page'));
    }

    private function arrivalDateTime(mixed $checkIn, ?string $time): ?CarbonImmutable
    {
        $time = trim((string) $time);

        if ($time === '') {
            return null;
        }

        return CarbonImmutable::parse($checkIn->check_in_date->format('Y-m-d').' '.$time);
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'actualArrivalTime' => __('check_in.validation.attributes.actual_arrival_time'),
            'beforePhoto' => __('check_in.validation.attributes.before_photo'),
            'problemPhoto' => __('check_in.validation.attributes.problem_photo'),
            'problemType' => __('check_in.validation.attributes.problem_type'),
            'severity' => __('check_in.validation.attributes.severity'),
            'description' => __('check_in.validation.attributes.description'),
            'mediaCaption' => __('check_in.validation.attributes.caption'),
        ];
    }
}
