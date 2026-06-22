<?php

namespace App\Livewire\Bookings\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingNotificationsCard extends Component
{
    public int $bookingId;

    #[Computed]
    public function notifications(): mixed
    {
        return Notification::query()
            ->select(['id', 'booking_id', 'recipient_user_id', 'user_id', 'title_translation_key', 'body_translation_key', 'data', 'translation_params_json', 'created_at'])
            ->forUser(auth()->user())
            ->where('booking_id', $this->bookingId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.bookings.notifications.booking-notifications-card', [
            'notifications' => $this->notifications,
        ]);
    }
}
