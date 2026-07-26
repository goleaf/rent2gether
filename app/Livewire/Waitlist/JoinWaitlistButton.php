<?php

namespace App\Livewire\Waitlist;

use App\Data\Waitlist\DateRange;
use App\Data\Waitlist\WaitlistContext;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Services\Waitlist\WaitlistService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class JoinWaitlistButton extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    public string $source = 'unknown';

    public bool $joined = false;

    public bool $offered = false;

    public function mount(int $sleepingPlaceId, string $checkIn = '', string $checkOut = '', int $guestsCount = 1, string $source = 'unknown'): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->guestsCount = max(1, $guestsCount);
        $this->source = $source;
        $this->refreshState();
    }

    public function join(WaitlistService $waitlist): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            session()->put('intended_waitlist', [
                'sleeping_place_id' => $this->sleepingPlaceId,
                'check_in' => $this->checkIn,
                'check_out' => $this->checkOut,
            ]);

            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $place = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'currency'])
            ->findOrFail($this->sleepingPlaceId);

        $waitlist->join($user, $place, new WaitlistContext(
            desiredCheckIn: $this->checkIn ?: now()->addWeek()->toDateString(),
            desiredCheckOut: $this->checkOut ?: now()->addWeek()->addDay()->toDateString(),
            guestsCount: max(1, $this->guestsCount),
            source: $this->source,
            readyToBookImmediately: true,
            notifyAvailable: true,
            notifyPriceDrop: true,
        ));

        $this->refreshState();
        $this->dispatch('waitlist-updated');
    }

    public function render(): View
    {
        return view('livewire.waitlist.join-waitlist-button');
    }

    private function refreshState(): void
    {
        if (! auth()->check()) {
            $this->joined = false;
            $this->offered = false;

            return;
        }

        $item = WaitlistItem::query()
            ->select(['id', 'user_id', 'sleeping_place_id', 'status', 'desired_check_in_date', 'desired_check_out_date'])
            ->where('user_id', auth()->id())
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->forDateRange(new DateRange(
                $this->checkIn ?: now()->addWeek()->toDateString(),
                $this->checkOut ?: now()->addWeek()->addDay()->toDateString(),
            ))
            ->open()
            ->first();

        $this->joined = $item instanceof WaitlistItem;
        $this->offered = $item?->status === 'offered';
    }
}
