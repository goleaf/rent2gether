<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingForgottenItem;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingForgottenItemService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createForgottenItem(User $host, BookingCheckOut $checkOut, array $data): BookingForgottenItem
    {
        $this->authorizeHost($host, $checkOut);

        $item = BookingForgottenItem::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $checkOut->booking_id,
            'guest_user_id' => $checkOut->guest_user_id,
            'host_user_id' => $checkOut->host_user_id,
            'item_name' => $data['item_name'] ?? null,
            'description' => $data['description'] ?? null,
            'photo_paths_json' => $data['photo_paths'] ?? [],
            'storage_location' => $data['storage_location'] ?? null,
            'status' => 'found',
            'keep_until' => $data['keep_until'] ?? null,
        ]);

        $checkOut->forceFill([
            'has_forgotten_items' => true,
            'status' => 'problem_reported',
            'problem_status' => 'forgotten_items',
        ])->save();

        return $item->refresh();
    }

    public function notifyGuest(BookingForgottenItem $item): void
    {
        $item->forceFill([
            'status' => 'guest_notified',
            'guest_notified_at' => now(),
        ])->save();
    }

    public function markPickedUp(User $host, BookingForgottenItem $item): BookingForgottenItem
    {
        $this->authorizeHost($host, $item->checkOut);

        $item->forceFill([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ])->save();

        return $item->refresh();
    }

    public function markDisposed(User $host, BookingForgottenItem $item): BookingForgottenItem
    {
        $this->authorizeHost($host, $item->checkOut);

        $item->forceFill([
            'status' => 'disposed',
            'disposed_at' => now(),
        ])->save();

        return $item->refresh();
    }

    private function authorizeHost(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }
    }
}
