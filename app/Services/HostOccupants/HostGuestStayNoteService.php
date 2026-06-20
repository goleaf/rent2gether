<?php

namespace App\Services\HostOccupants;

use App\Models\Booking;
use App\Models\HostGuestStayNote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HostGuestStayNoteService
{
    public function __construct(
        private readonly HostOccupantPrivacyService $privacy,
        private readonly HostCurrentStaySnapshotService $snapshots,
    ) {}

    public function createNote(User $host, Booking $booking, string $note, string $importance): HostGuestStayNote
    {
        $this->authorizeBooking($host, $booking);

        $created = HostGuestStayNote::query()->create([
            'user_id' => $host->id,
            'guest_user_id' => $booking->guest_user_id,
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'note' => $note,
            'importance' => $importance,
            'is_pinned' => false,
        ]);

        $this->snapshots->refreshForBooking($booking);

        return $created;
    }

    public function updateNote(User $host, HostGuestStayNote $note, array $data): HostGuestStayNote
    {
        $this->authorizeNote($host, $note);

        $note->fill(array_intersect_key($data, array_flip(['note', 'importance', 'is_pinned'])))->save();

        $this->snapshots->refreshForBooking($note->booking);

        return $note->refresh();
    }

    public function deleteNote(User $host, HostGuestStayNote $note): void
    {
        $this->authorizeNote($host, $note);
        $booking = $note->booking;

        $note->delete();

        $this->snapshots->refreshForBooking($booking);
    }

    public function pinNote(User $host, HostGuestStayNote $note): HostGuestStayNote
    {
        $this->authorizeNote($host, $note);

        $note->forceFill(['is_pinned' => true])->save();

        $this->snapshots->refreshForBooking($note->booking);

        return $note->refresh();
    }

    public function getNotesForBooking(User $host, Booking $booking): Collection
    {
        $this->authorizeBooking($host, $booking);

        return HostGuestStayNote::query()
            ->where('user_id', $host->id)
            ->where('booking_id', $booking->id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('id')
            ->get();
    }

    private function authorizeBooking(User $host, Booking $booking): void
    {
        if (! $this->privacy->canViewOccupant($host, $booking)) {
            throw new AuthorizationException;
        }
    }

    private function authorizeNote(User $host, HostGuestStayNote $note): void
    {
        if ((int) $note->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }
    }
}
