<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchItem;
use App\Models\BookingListingMismatchReport;
use Illuminate\Support\Collection;

class ListingMismatchItemService
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, BookingListingMismatchItem>
     */
    public function createItemsFromReport(BookingListingMismatchReport $report, array $items): Collection
    {
        return collect($items)->map(function (array $item) use ($report): BookingListingMismatchItem {
            return $report->items()->create([
                'item_key' => (string) ($item['item_key'] ?? $report->mismatch_type),
                'item_type' => (string) ($item['item_type'] ?? $this->itemTypeForMismatch((string) $report->mismatch_type)),
                'promised_value' => $item['promised_value'] ?? null,
                'actual_value' => $item['actual_value'] ?? null,
                'snapshot_source_type' => $item['snapshot_source_type'] ?? 'booking_listing_snapshot',
                'snapshot_source_id' => $item['snapshot_source_id'] ?? null,
                'is_confirmed' => $item['is_confirmed'] ?? null,
                'confidence_score' => $item['confidence_score'] ?? null,
                'severity' => (string) ($item['severity'] ?? $report->severity),
                'guest_note' => $item['guest_note'] ?? null,
                'host_note' => $item['host_note'] ?? null,
            ]);
        });
    }

    public function confirmItem(BookingListingMismatchItem $item): BookingListingMismatchItem
    {
        $item->forceFill(['is_confirmed' => true])->save();

        return $item->fresh();
    }

    public function rejectItem(BookingListingMismatchItem $item): BookingListingMismatchItem
    {
        $item->forceFill(['is_confirmed' => false])->save();

        return $item->fresh();
    }

    /**
     * @return Collection<int, BookingListingMismatchItem>
     */
    public function getConfirmedItems(BookingListingMismatchReport $report): Collection
    {
        return $report->items()->where('is_confirmed', true)->get();
    }

    /**
     * @return Collection<int, BookingListingMismatchItem>
     */
    public function getHighSeverityItems(BookingListingMismatchReport $report): Collection
    {
        return $report->items()->whereIn('severity', ['high', 'urgent', 'unsafe'])->get();
    }

    private function itemTypeForMismatch(string $mismatchType): string
    {
        return match ($mismatchType) {
            'wrong_sleeping_place', 'wrong_bed_type', 'wrong_bunk_level', 'missing_locker', 'missing_lock', 'missing_bedding', 'missing_towel', 'missing_pillow', 'missing_blanket', 'missing_privacy_curtain', 'missing_socket' => 'sleeping_place_feature',
            'missing_wifi', 'missing_hot_water', 'missing_heating', 'missing_air_conditioning', 'kitchen_not_available', 'bathroom_not_available', 'washing_machine_not_available' => 'property_amenity',
            'dirty_sleeping_place', 'dirty_room', 'dirty_property', 'bad_smell', 'mold', 'insects' => 'cleanliness',
            'wrong_address' => 'address',
            'more_people_than_listed' => 'occupancy',
            'noise_level_mismatch' => 'noise',
            'safety_mismatch' => 'safety',
            'photos_mismatch' => 'photo',
            default => 'other',
        };
    }
}
