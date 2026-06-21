<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationAlternative;
use Illuminate\Support\Collection;

class BookingCancellationAlternativeService
{
    /**
     * @return Collection<int, BookingCancellationAlternative>
     */
    public function suggestAlternatives(BookingCancellation $cancellation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, BookingCancellationAlternative>
     */
    public function suggestSameHostPlaces(BookingCancellation $cancellation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, BookingCancellationAlternative>
     */
    public function suggestSameAreaPlaces(BookingCancellation $cancellation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, BookingCancellationAlternative>
     */
    public function suggestSimilarPricePlaces(BookingCancellation $cancellation): Collection
    {
        return collect();
    }

    /**
     * @return Collection<int, BookingCancellationAlternative>
     */
    public function createAlternativeRecords(BookingCancellation $cancellation): Collection
    {
        $alternative = $cancellation->alternatives()->create([
            'suggestion_type' => 'saved_search',
            'check_in_date' => $cancellation->check_in_date,
            'check_out_date' => $cancellation->check_out_date,
            'currency' => $cancellation->currency,
            'message_key' => 'cancellations.messages.alternatives_available',
            'sort_order' => 10,
        ]);

        return collect([$alternative]);
    }
}
