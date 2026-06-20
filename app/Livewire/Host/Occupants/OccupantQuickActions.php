<?php

namespace App\Livewire\Host\Occupants;

use App\Models\Booking;
use App\Services\HostOccupants\HostGuestStayNoteService;
use App\Services\HostOccupants\HostOccupantActionService;

class OccupantQuickActions extends BaseHostOccupantsComponent
{
    public string $section = 'quick_actions';

    public bool $needsConfirmation = false;

    public ?string $preparedAction = null;

    /**
     * @var list<string>
     */
    private array $dangerousActions = [
        'mark_checked_out',
        'mark_no_show',
        'start_checkout_process',
        'resolve_deposit_issue',
        'create_repair_inspection',
        'external_contact',
    ];

    public function prepareAction(string $action): void
    {
        $this->preparedAction = $action;
        $this->needsConfirmation = in_array($action, $this->dangerousActions, true);
    }

    public function markCheckedIn(int $bookingId): void
    {
        $booking = Booking::query()->findOrFail($bookingId);

        app(HostOccupantActionService::class)->markCheckedIn(auth()->user(), $booking);

        $this->preparedAction = null;
        $this->needsConfirmation = false;
    }

    public function markCheckedOut(int $bookingId): void
    {
        $booking = Booking::query()->findOrFail($bookingId);

        app(HostOccupantActionService::class)->markCheckedOut(auth()->user(), $booking);

        $this->preparedAction = null;
        $this->needsConfirmation = false;
    }

    public function createCleaningTask(int $bookingId): void
    {
        $booking = Booking::query()->findOrFail($bookingId);

        app(HostOccupantActionService::class)->createCleaningTask(auth()->user(), $booking);
    }

    public function addNote(int $bookingId, string $note, string $importance = 'normal'): void
    {
        $booking = Booking::query()->findOrFail($bookingId);

        app(HostGuestStayNoteService::class)->createNote(auth()->user(), $booking, $note, $importance);
    }
}
