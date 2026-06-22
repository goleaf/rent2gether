<?php

namespace App\Livewire\Checkin;

use App\Actions\Bookings\ReportCheckInProblem;
use App\Actions\Media\StoreOptimizedImageAction;
use App\Livewire\Trips\Concerns\LoadsTripBookings;
use App\Models\Booking;
use App\Models\User;
use App\Support\Trips\TripBookingPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ProblemReport extends Component
{
    use LoadsTripBookings;
    use WithFileUploads;

    #[Locked]
    public int $bookingId;

    public string $problemDescription = '';

    /**
     * @var list<TemporaryUploadedFile>
     */
    public array $photos = [];

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $booking->guest_user_id === (int) $user->id, 403);

        $this->bookingId = $booking->id;
    }

    public function submit(ReportCheckInProblem $reportProblem, StoreOptimizedImageAction $images): void
    {
        $validated = $this->validate([
            'problemDescription' => ['required', 'string', 'min:10', 'max:2000'],
            'photos' => ['array', 'max:6'],
            'photos.*' => ['image', 'max:2048'],
        ], [], app('translator')->get('booking.problem_report.validation_attributes'));

        $paths = collect($validated['photos'] ?? [])
            ->map(fn (TemporaryUploadedFile $photo): string => $images->handle($photo, 'checkin-problems')['mobile_path'])
            ->values()
            ->all();

        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $reportProblem->handle($user, $this->booking(), [
            'problem_description' => $validated['problemDescription'],
            'problem_media' => $paths,
        ]);

        session()->flash('trip-status', __('notifications.flash.checkin_problem_reported'));

        $this->redirectRoute('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $this->bookingId,
        ], navigate: true);
    }

    public function render(TripBookingPresenter $presenter): View
    {
        $booking = $this->booking();

        return view('livewire.checkin.problem-report', [
            'booking' => $booking,
            'trip' => $presenter->detail($booking),
        ])->layout('layouts.app', [
            'title' => __('booking.problem_report.title'),
        ]);
    }

    private function booking(): Booking
    {
        return $this->tripBookingQuery()
            ->forGuest((int) auth()->id())
            ->findOrFail($this->bookingId);
    }
}
