<?php

namespace App\Services\HostCalendar;

use App\Models\Booking;
use App\Models\HostCalendarEvent;
use App\Models\HostCalendarNote;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostCalendar\Data\HostCalendarFilters;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HostCalendarNoteService
{
    public function createNote(User $host, array $data): HostCalendarNote
    {
        $this->authorizePayload($host, $data);

        $note = HostCalendarNote::query()->create([
            'user_id' => $host->id,
            'property_id' => $data['property_id'] ?? null,
            'room_id' => $data['room_id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'note_date' => $data['note_date'],
            'note_type' => $data['note_type'] ?? 'general',
            'note' => $data['note'],
            'is_private' => $data['is_private'] ?? true,
        ]);

        if ($note->property_id) {
            HostCalendarEvent::query()->create([
                'user_id' => $host->id,
                'property_id' => $note->property_id,
                'room_id' => $note->room_id,
                'sleeping_place_id' => $note->sleeping_place_id,
                'booking_id' => $note->booking_id,
                'event_type' => 'note',
                'event_status' => 'active',
                'event_date' => $note->note_date,
                'title_key' => 'host_calendar.event_titles.note',
                'host_note' => $note->note,
                'priority' => 10,
                'source' => 'host_note',
                'is_private' => true,
            ]);
        }

        return $note;
    }

    public function updateNote(User $host, HostCalendarNote $note, array $data): HostCalendarNote
    {
        if ((int) $note->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        $note->fill(array_intersect_key($data, array_flip(['note_date', 'note_type', 'note', 'is_private'])))->save();

        HostCalendarEvent::query()
            ->where('source', 'host_note')
            ->where('user_id', $host->id)
            ->where('booking_id', $note->booking_id)
            ->whereDate('event_date', $note->note_date)
            ->update(['host_note' => $note->note]);

        return $note->refresh();
    }

    public function deleteNote(User $host, HostCalendarNote $note): void
    {
        if ((int) $note->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        $note->delete();
    }

    public function getNotes(User $host, array $range, HostCalendarFilters $filters): Collection
    {
        return HostCalendarNote::query()
            ->select(['id', 'user_id', 'property_id', 'room_id', 'sleeping_place_id', 'booking_id', 'note_date', 'note_type', 'note', 'is_private'])
            ->where('user_id', $host->id)
            ->whereDate('note_date', '>=', $range['start'])
            ->whereDate('note_date', '<', $range['end'])
            ->when($filters->propertyId, fn ($query) => $query->where('property_id', $filters->propertyId))
            ->when($filters->roomId, fn ($query) => $query->where('room_id', $filters->roomId))
            ->when($filters->sleepingPlaceId, fn ($query) => $query->where('sleeping_place_id', $filters->sleepingPlaceId))
            ->orderBy('note_date')
            ->orderBy('id')
            ->get();
    }

    private function authorizePayload(User $host, array $data): void
    {
        if (isset($data['property_id']) && ! Property::query()->where('id', $data['property_id'])->where('host_user_id', $host->id)->exists()) {
            throw new AuthorizationException;
        }

        if (isset($data['room_id']) && ! Room::query()->where('id', $data['room_id'])->whereHas('property', fn ($property) => $property->where('host_user_id', $host->id))->exists()) {
            throw new AuthorizationException;
        }

        if (isset($data['sleeping_place_id']) && ! SleepingPlace::query()->where('id', $data['sleeping_place_id'])->whereHas('property', fn ($property) => $property->where('host_user_id', $host->id))->exists()) {
            throw new AuthorizationException;
        }

        if (isset($data['booking_id']) && ! Booking::query()->where('id', $data['booking_id'])->where('host_user_id', $host->id)->exists()) {
            throw new AuthorizationException;
        }
    }
}
