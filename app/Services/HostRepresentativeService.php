<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HostRepresentative;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HostRepresentativeService
{
    private const FIELDS = [
        'representative_user_id',
        'name',
        'phone',
        'email',
        'role_description',
        'can_help_with_check_in',
        'can_help_with_keys',
        'can_help_with_cleaning_coordination',
        'can_be_contacted_by_guest',
        'visible_after_booking_only',
        'active',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $host, array $data): HostRepresentative
    {
        return HostRepresentative::query()->create([
            ...Arr::only($data, self::FIELDS),
            'host_user_id' => $host->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $host, HostRepresentative $representative, array $data): HostRepresentative
    {
        $this->ensureOwner($host, $representative);
        $representative->fill(Arr::only($data, self::FIELDS));
        $representative->save();

        return $representative->refresh();
    }

    public function deactivate(User $host, HostRepresentative $representative): HostRepresentative
    {
        $this->ensureOwner($host, $representative);
        $representative->forceFill(['active' => false])->save();

        return $representative->refresh();
    }

    /**
     * @return Collection<int, HostRepresentative>
     */
    public function getVisibleRepresentativesForBooking(Booking $booking): Collection
    {
        return HostRepresentative::query()
            ->where('host_user_id', $booking->host_user_id)
            ->where('active', true)
            ->where('can_be_contacted_by_guest', true)
            ->orderBy('id')
            ->get();
    }

    private function ensureOwner(User $host, HostRepresentative $representative): void
    {
        abort_if((int) $representative->host_user_id !== (int) $host->id, 403);
    }
}
