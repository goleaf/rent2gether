<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class BookingRequestAlternativeService
{
    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestSameRoomAlternatives(BookingRequest $request): Collection
    {
        return $this->baseAlternatives($request)
            ->where('room_id', $request->room_id)
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestSamePropertyAlternatives(BookingRequest $request): Collection
    {
        return $this->baseAlternatives($request)
            ->where('property_id', $request->property_id)
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestSameHostAlternatives(BookingRequest $request): Collection
    {
        return $this->baseAlternatives($request)
            ->where('user_id', $request->host_user_id)
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestSimilarPlaces(BookingRequest $request): Collection
    {
        return $this->baseAlternatives($request)
            ->where('place_type', $request->sleepingPlace?->place_type)
            ->limit(6)
            ->get();
    }

    public function suggestSavedSearchAfterReject(BookingRequest $request): mixed
    {
        return [
            'message_key' => 'booking_requests.suggestions.save_search_after_reject',
            'check_in_date' => $request->check_in_date?->toDateString(),
            'check_out_date' => $request->check_out_date?->toDateString(),
            'guests_count' => (int) $request->guests_count,
        ];
    }

    private function baseAlternatives(BookingRequest $request): mixed
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'user_id', 'title', 'display_name', 'place_type', 'base_price', 'currency', 'publication_status'])
            ->whereKeyNot($request->sleeping_place_id)
            ->where(function ($query): void {
                $query->whereNull('publication_status')->orWhere('publication_status', 'published');
            })
            ->orderBy('id');
    }
}
