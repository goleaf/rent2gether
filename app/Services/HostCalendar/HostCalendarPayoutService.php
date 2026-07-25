<?php

namespace App\Services\HostCalendar;

use App\Models\HostCalendarEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class HostCalendarPayoutService
{
    public function getPayoutEvents(User $host, array $range): Collection
    {
        return $this->query($host, $range)->get();
    }

    public function getExpectedPayouts(User $host, array $range): Collection
    {
        return $this->query($host, $range)->where('payout_status', 'expected')->get();
    }

    public function getPaidPayouts(User $host, array $range): Collection
    {
        return $this->query($host, $range)->whereIn('payout_status', ['completed', 'paid'])->get();
    }

    public function getPendingPayouts(User $host, array $range): Collection
    {
        return $this->query($host, $range)->whereIn('payout_status', ['pending', 'processing'])->get();
    }

    private function query(User $host, array $range)
    {
        return HostCalendarEvent::query()
            ->where('user_id', $host->id)
            ->where('event_type', 'payout')
            ->where('event_date', '>=', $range['start'])
            ->where('event_date', '<', $range['end'])
            ->orderBy('event_date');
    }
}
