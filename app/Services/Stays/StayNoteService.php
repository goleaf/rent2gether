<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\BookingStayNote;
use App\Models\User;
use Illuminate\Support\Collection;

class StayNoteService
{
    public function addGuestNote(User $guest, BookingStay $stay, string $note): BookingStayNote
    {
        abort_unless((int) $stay->guest_user_id === (int) $guest->id, 403);

        return $this->createNote($guest, $stay, 'guest_note', 'guest_and_host', $note);
    }

    public function addHostNote(User $host, BookingStay $stay, string $note): BookingStayNote
    {
        abort_unless((int) $stay->host_user_id === (int) $host->id, 403);

        return $this->createNote($host, $stay, 'host_note', 'host_only', $note);
    }

    public function addInternalHostNote(User $host, BookingStay $stay, string $note): BookingStayNote
    {
        abort_unless((int) $stay->host_user_id === (int) $host->id, 403);

        return $this->createNote($host, $stay, 'host_note', 'internal', $note);
    }

    /**
     * @return Collection<int, BookingStayNote>
     */
    public function getVisibleNotes(User $user, BookingStay $stay): Collection
    {
        $query = $stay->notes()->orderBy('created_at')->orderBy('id');

        if ((int) $stay->host_user_id === (int) $user->id) {
            return $query->whereIn('visibility', ['guest_and_host', 'host_only', 'internal'])->get();
        }

        if ((int) $stay->guest_user_id === (int) $user->id) {
            return $query->whereIn('visibility', ['guest_and_host', 'guest_only'])->get();
        }

        return collect();
    }

    private function createNote(User $user, BookingStay $stay, string $type, string $visibility, string $note): BookingStayNote
    {
        return $stay->notes()->create([
            'booking_id' => $stay->booking_id,
            'user_id' => $user->id,
            'note_type' => $type,
            'visibility' => $visibility,
            'note' => $note,
        ]);
    }
}
