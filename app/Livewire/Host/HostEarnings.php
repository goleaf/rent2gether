<?php

namespace App\Livewire\Host;

use App\Models\Booking;
use App\Models\Payout;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostEarnings extends Component
{
    #[Computed]
    public function todayIncome(): float
    {
        return (float) Booking::where('host_id', auth()->id())
            ->whereIn('status', ['completed', 'checked_out', 'checked_in', 'active_stay'])
            ->where('check_in', now()->toDateString())
            ->sum('subtotal');
    }

    #[Computed]
    public function weekIncome(): float
    {
        return (float) Booking::where('host_id', auth()->id())
            ->whereIn('status', ['completed', 'checked_out', 'checked_in', 'active_stay'])
            ->where('check_in', '>=', now()->startOfWeek()->toDateString())
            ->sum('subtotal');
    }

    #[Computed]
    public function monthIncome(): float
    {
        return (float) Booking::where('host_id', auth()->id())
            ->whereIn('status', ['completed', 'checked_out', 'checked_in', 'active_stay'])
            ->where('check_in', '>=', now()->startOfMonth()->toDateString())
            ->sum('subtotal');
    }

    #[Computed]
    public function yearIncome(): float
    {
        return (float) Booking::where('host_id', auth()->id())
            ->whereIn('status', ['completed', 'checked_out', 'checked_in', 'active_stay'])
            ->where('check_in', '>=', now()->startOfYear()->toDateString())
            ->sum('subtotal');
    }

    #[Computed]
    public function recentPayouts()
    {
        return Payout::where('host_id', auth()->id())
            ->with('booking')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.host.host-earnings');
    }
}
