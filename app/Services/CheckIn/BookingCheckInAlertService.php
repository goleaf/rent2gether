<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAlert;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class BookingCheckInAlertService
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function createAlert(BookingCheckIn $checkIn, string $alertType, string $severity, array $params): BookingCheckInAlert
    {
        $alert = BookingCheckInAlert::query()->create([
            'booking_check_in_id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
            'guest_user_id' => $checkIn->guest_user_id,
            'host_user_id' => $checkIn->host_user_id,
            'alert_type' => $alertType,
            'severity' => $severity,
            'status' => 'open',
            'message_key' => 'check_in.alerts.'.$alertType,
            'message_params_json' => $params,
        ]);

        if (in_array($alertType, ['check_in_problem', 'guest_arrived'], true)) {
            $this->notifyHost($alert);
        }

        return $alert->refresh();
    }

    public function notifyHost(BookingCheckInAlert $alert): void
    {
        $alert->forceFill(['status' => 'notified_host'])->save();
    }

    public function resolveAlert(User $host, BookingCheckInAlert $alert): BookingCheckInAlert
    {
        if ((int) $alert->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_in.validation.not_host_booking'));
        }

        $alert->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        return $alert->refresh();
    }

    /**
     * @return Collection<int, BookingCheckInAlert>
     */
    public function getOpenAlertsForHost(User $host): Collection
    {
        return BookingCheckInAlert::query()
            ->where('host_user_id', $host->id)
            ->whereIn('status', ['open', 'notified_host', 'waiting', 'escalated'])
            ->orderByDesc('id')
            ->get();
    }
}
