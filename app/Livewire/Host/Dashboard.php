<?php

namespace App\Livewire\Host;

use App\Enums\BookingStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function properties()
    {
        return Property::where('user_id', auth()->id())
            ->withCount('rooms', 'beds')
            ->get();
    }

    #[Computed]
    public function upcomingCheckIns()
    {
        return Booking::where('host_id', auth()->id())
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Paid->value, BookingStatus::ReadyForCheckIn->value])
            ->where('check_in', '>=', now()->toDateString())
            ->where('check_in', '<=', now()->addDays(7)->toDateString())
            ->with(['guest', 'bed.room'])
            ->orderBy('check_in')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function upcomingCheckOuts()
    {
        return Booking::where('host_id', auth()->id())
            ->whereIn('status', [BookingStatus::CheckedIn->value, BookingStatus::ActiveStay->value, BookingStatus::LeavingSoon->value])
            ->where('check_out', '>=', now()->toDateString())
            ->where('check_out', '<=', now()->addDays(7)->toDateString())
            ->with(['guest', 'bed.room'])
            ->orderBy('check_out')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function pendingRequests()
    {
        return Booking::where('host_id', auth()->id())
            ->where('status', BookingStatus::PendingHostConfirmation->value)
            ->with(['guest', 'bed.room'])
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function activeGuests()
    {
        return Booking::where('host_id', auth()->id())
            ->whereIn('status', [BookingStatus::CheckedIn->value, BookingStatus::ActiveStay->value])
            ->with(['guest', 'bed.room'])
            ->orderBy('check_out')
            ->get();
    }

    #[Computed]
    public function monthlyIncome(): float
    {
        return (float) Booking::where('host_id', auth()->id())
            ->whereIn('status', [
                BookingStatus::Completed->value, BookingStatus::CheckedOut->value,
                BookingStatus::CheckedIn->value, BookingStatus::ActiveStay->value,
            ])
            ->where('check_in', '>=', now()->startOfMonth()->toDateString())
            ->sum('subtotal');
    }

    #[Computed]
    public function totalFreeBeds(): int
    {
        $propertyIds = Property::where('user_id', auth()->id())->pluck('id');

        return Bed::query()
            ->active()
            ->whereHas('room', fn ($q) => $q->whereIn('property_id', $propertyIds))
            ->whereDoesntHave('bookings', fn ($q) => $q
                ->whereNotIn('status', ['cancelled_guest', 'cancelled_host', 'cancelled_system', 'no_show'])
                ->whereDate('check_in', '<=', now()->toDateString())
                ->whereDate('check_out', '>', now()->toDateString())
            )
            ->count();
    }

    public function render(): View
    {
        return view('livewire.host.dashboard');
    }
}
