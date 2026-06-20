<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintType;
use App\Models\Booking;
use App\Models\User;
use App\Services\Complaints\ComplaintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CreateComplaint extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $bookingId;

    public string $reporterRole = 'guest';

    public string $type = '';

    public string $priority = 'normal';

    public string $description = '';

    public string $desiredResolution = '';

    public bool $refundRequested = false;

    public bool $depositHoldRequested = false;

    /**
     * @var list<TemporaryUploadedFile>
     */
    public array $media = [];

    public function mount(Booking $booking): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);
        abort_unless($this->isParticipant($booking, $user), 403);

        $this->bookingId = $booking->id;
        $this->reporterRole = $this->isHost($booking, $user) ? 'host' : 'guest';
    }

    public function submit(ComplaintService $complaints): void
    {
        $allowedTypes = array_keys($this->complaintTypes());

        $validated = $this->validate([
            'type' => ['required', 'string', 'in:'.implode(',', $allowedTypes)],
            'priority' => ['required', 'string', 'in:low,normal,high,critical'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'desiredResolution' => ['nullable', 'string', 'max:1000'],
            'refundRequested' => ['boolean'],
            'depositHoldRequested' => ['boolean'],
            'media' => ['array', 'max:6'],
            'media.*' => ['image', 'max:2048'],
        ], [], app('translator')->get('booking.complaint.validation_attributes'));

        $paths = collect($validated['media'] ?? [])
            ->map(fn (TemporaryUploadedFile $file): string => $file->store('complaints', 'public'))
            ->values()
            ->all();

        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $complaint = $complaints->createForBooking(
            booking: $this->booking(),
            reporter: $user,
            type: $validated['type'],
            priority: $validated['priority'],
            description: $validated['description'],
            desiredResolution: $validated['desiredResolution'] ?: null,
            refundRequested: (bool) ($validated['refundRequested'] ?? false),
            depositHoldRequested: (bool) ($validated['depositHoldRequested'] ?? false),
            media: $paths,
        );

        session()->flash('complaint-status', __('notifications.flash.complaint_submitted', ['reference' => $complaint->complaint_number]));

        $this->redirectRoute('complaints.show', [
            'locale' => app()->getLocale(),
            'complaint' => $complaint,
        ], navigate: true);
    }

    public function complaintTypes(): array
    {
        $types = $this->reporterRole === 'host'
            ? ComplaintType::hostTypes()
            : ComplaintType::guestTypes();

        return collect($types)->mapWithKeys(
            fn (ComplaintType $type) => [$type->value => $type->label()]
        )->all();
    }

    public function render(): View
    {
        $booking = $this->booking();

        return view('livewire.complaints.create-complaint', [
            'booking' => $booking,
            'summaryTitle' => $this->summaryTitle($booking),
        ])->layout('layouts.app', [
            'title' => __('booking.complaint.title'),
        ]);
    }

    private function booking(): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_id',
                'guest_user_id',
                'host_id',
                'host_user_id',
                'bed_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'check_in_date',
                'check_out_date',
            ])
            ->with([
                'bed:id,title',
                'property:id,title,city,district',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->findOrFail($this->bookingId);
    }

    private function summaryTitle(Booking $booking): string
    {
        return $booking->sleepingPlace?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $booking->sleepingPlace?->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
            ?: $booking->sleepingPlace?->display_name
            ?: $booking->bed?->title
            ?: $booking->reference;
    }

    private function isParticipant(Booking $booking, User $user): bool
    {
        return in_array((int) $user->id, [
            (int) $booking->guest_user_id,
            (int) $booking->guest_id,
            (int) $booking->host_user_id,
            (int) $booking->host_id,
        ], true);
    }

    private function isHost(Booking $booking, User $user): bool
    {
        return in_array((int) $user->id, [
            (int) $booking->host_user_id,
            (int) $booking->host_id,
        ], true);
    }
}
