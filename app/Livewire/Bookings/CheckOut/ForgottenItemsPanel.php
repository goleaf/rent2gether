<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Models\BookingForgottenItem;
use App\Services\CheckOut\BookingForgottenItemService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ForgottenItemsPanel extends Component
{
    use LoadsBookingCheckOut;

    public string $itemName = '';

    public string $description = '';

    public string $storageLocation = '';

    public string $keepUntil = '';

    public function createItem(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingForgottenItemService::class)->createForgottenItem(Auth::user(), $checkOut, [
                'item_name' => $this->itemName,
                'description' => $this->description,
                'storage_location' => $this->storageLocation,
                'keep_until' => $this->keepUntil ?: null,
            ]);
            $this->refreshCheckOutState();
        }
    }

    public function markPickedUp(int $itemId): void
    {
        $item = BookingForgottenItem::query()->findOrFail($itemId);

        if (Auth::user()) {
            app(BookingForgottenItemService::class)->markPickedUp(Auth::user(), $item);
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('forgotten_items'));
    }
}
