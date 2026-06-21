<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingRelocation;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class HostUnresponsiveRelocationIntegrationService
{
    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestRelocationOptions(BookingHostUnresponsiveCase $case): Collection
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'display_name', 'title', 'currency'])
            ->where('property_id', $case->property_id)
            ->whereKeyNot($case->sleeping_place_id)
            ->limit(5)
            ->get();
    }

    public function createRelocationRequest(BookingHostUnresponsiveCase $case, ?SleepingPlace $place = null): mixed
    {
        $relocation = app(BookingRelocationService::class)->createFromGuestRequest($case->guest()->firstOrFail(), $case->booking()->firstOrFail(), [
            'reason' => 'host_unresponsive',
            'source_type' => 'host_unresponsive',
            'source_id' => $case->id,
            'new_sleeping_place_id' => $place?->id,
            'relocation_date' => now()->toDateString(),
            'guest_comment' => $case->guest_comment,
        ]);

        if ($relocation instanceof BookingRelocation) {
            $case->forceFill(['booking_relocation_id' => $relocation->id])->save();
            app(HostUnresponsiveEventService::class)->record($case->fresh(), 'relocation_created', ['relocation_id' => $relocation->id]);
        }

        return $relocation;
    }
}
