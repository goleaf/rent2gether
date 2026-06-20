<?php

namespace App\Services\HostBulk;

use App\Enums\MessageThreadType;
use App\Models\Booking;
use App\Models\User;
use App\Services\Messaging\MessageService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class HostBulkMessageService
{
    public function __construct(
        private readonly HostBulkPermissionService $permissions,
        private readonly MessageService $messages,
    ) {}

    public function previewRecipients(User $host, array $filters): Collection
    {
        $query = Booking::query()
            ->with([
                'guest:id,name',
                'property:id,host_user_id,user_id,title',
                'sleepingPlace:id,property_id,display_name',
            ])
            ->where('host_user_id', $host->id)
            ->whereNotNull('guest_user_id');

        if (! empty($filters['period_start']) && ! empty($filters['period_end'])) {
            $query->whereDate('check_in_date', '<', $filters['period_end'])
                ->whereDate('check_out_date', '>', $filters['period_start']);
        }

        if (! empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('check_in_date')->get()->unique('id')->values();
    }

    public function sendToCurrentGuests(User $host, array $filters, string $message): array
    {
        $filters['period_start'] ??= now()->toDateString();
        $filters['period_end'] ??= now()->addDay()->toDateString();

        return $this->sendToBookingGuests($host, $this->previewRecipients($host, $filters), $message);
    }

    public function sendToFutureGuests(User $host, array $filters, string $message): array
    {
        $filters['period_start'] ??= now()->toDateString();

        return $this->sendToBookingGuests($host, $this->previewRecipients($host, $filters), $message);
    }

    public function sendToBookingGuests(User $host, Collection $bookings, string $message): array
    {
        $message = trim($message);

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => __('host_bulk.errors.empty_message'),
            ]);
        }

        $sent = 0;
        $skipped = 0;
        $seen = [];

        foreach ($bookings as $booking) {
            if (! $booking instanceof Booking || isset($seen[$booking->id])) {
                $skipped++;

                continue;
            }

            $seen[$booking->id] = true;
            $this->permissions->ensureHostCanMessageBookingGuest($host, $booking);
            $booking->loadMissing(['guest', 'property', 'sleepingPlace']);

            if (! $booking->guest) {
                $skipped++;

                continue;
            }

            $thread = $this->messages->getOrCreateThread(
                $booking->guest,
                $host,
                MessageThreadType::Booking,
                $booking,
                $booking->property,
                $booking->sleepingPlace,
            );
            $this->messages->send($thread, $host, $message);
            $sent++;
        }

        return [
            'selected_count' => $bookings->count(),
            'sent_count' => $sent,
            'affected_count' => $sent,
            'skipped_count' => $skipped,
            'failed_count' => 0,
        ];
    }
}
