<?php

namespace App\Livewire\Waitlist;

use App\Data\Waitlist\WaitlistContext;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Waitlist\WaitlistService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class JoinWaitlistSheet extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $desiredCheckIn = '';

    public string $desiredCheckOut = '';

    public int $guestsCount = 1;

    public string $maxPricePerNight = '';

    public string $maxTotalPrice = '';

    public string $maxDeposit = '';

    public bool $flexibleDates = false;

    public ?int $flexibleDays = null;

    public bool $readyToBookImmediately = true;

    public bool $autoSendRequest = false;

    public bool $notifyAvailable = true;

    public bool $notifyPriceDrop = true;

    public string $guestMessage = '';

    public string $expiresAt = '';

    public bool $joined = false;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->desiredCheckIn = now()->addWeek()->toDateString();
        $this->desiredCheckOut = now()->addWeek()->addDays(2)->toDateString();
    }

    public function join(WaitlistService $waitlist): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $validated = $this->validate([
            'desiredCheckIn' => ['required', 'date', 'after_or_equal:today'],
            'desiredCheckOut' => ['required', 'date', 'after:desiredCheckIn'],
            'guestsCount' => ['required', 'integer', 'min:1', 'max:20'],
            'maxPricePerNight' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'maxTotalPrice' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'maxDeposit' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'guestMessage' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'desiredCheckIn' => __('waitlist.check_in'),
            'desiredCheckOut' => __('waitlist.check_out'),
            'guestsCount' => __('waitlist.guests_count'),
            'maxPricePerNight' => __('waitlist.max_price'),
            'maxTotalPrice' => __('waitlist.max_total_price'),
            'maxDeposit' => __('waitlist.max_deposit'),
            'guestMessage' => __('waitlist.guest_message'),
        ]);

        $place = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'currency'])
            ->findOrFail($this->sleepingPlaceId);

        $waitlist->join($user, $place, new WaitlistContext(
            desiredCheckIn: $validated['desiredCheckIn'],
            desiredCheckOut: $validated['desiredCheckOut'],
            guestsCount: (int) $validated['guestsCount'],
            maxPricePerNight: $this->floatOrNull($validated['maxPricePerNight'] ?? null),
            maxTotalPrice: $this->floatOrNull($validated['maxTotalPrice'] ?? null),
            maxDeposit: $this->floatOrNull($validated['maxDeposit'] ?? null),
            source: 'sheet',
            flexibleDates: $this->flexibleDates,
            flexibleDays: $this->flexibleDays,
            readyToBookImmediately: $this->readyToBookImmediately,
            autoSendRequest: $this->autoSendRequest,
            notifyAvailable: $this->notifyAvailable,
            notifyPriceDrop: $this->notifyPriceDrop,
            guestMessage: $validated['guestMessage'] ?: null,
            expiresAt: $this->expiresAt ?: null,
        ));

        $this->joined = true;
        $this->dispatch('waitlist-updated');
    }

    public function render(): View
    {
        return view('livewire.waitlist.join-waitlist-sheet');
    }

    private function floatOrNull(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
