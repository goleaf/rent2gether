<?php

namespace App\Livewire\Waitlist;

use App\Models\User;
use App\Models\WaitlistItem;
use App\Services\Waitlist\WaitlistService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditWaitlistItemSheet extends Component
{
    #[Locked]
    public int $waitlistItemId;

    public string $desiredCheckIn = '';

    public string $desiredCheckOut = '';

    public int $guestsCount = 1;

    public string $maxPricePerNight = '';

    public string $maxTotalPrice = '';

    public string $maxDeposit = '';

    public bool $readyToBookImmediately = true;

    public bool $autoSendRequest = false;

    public bool $notifyAvailable = true;

    public bool $notifyPriceDrop = true;

    public string $guestMessage = '';

    public string $expiresAt = '';

    public bool $saved = false;

    public function mount(int $waitlistItemId): void
    {
        $this->waitlistItemId = $waitlistItemId;
        $item = WaitlistItem::query()->where('user_id', auth()->id())->findOrFail($waitlistItemId);
        $this->desiredCheckIn = $item->desired_check_in_date?->toDateString() ?? '';
        $this->desiredCheckOut = $item->desired_check_out_date?->toDateString() ?? '';
        $this->guestsCount = max(1, (int) $item->guests_count);
        $this->maxPricePerNight = $item->max_price_per_night !== null ? (string) $item->max_price_per_night : '';
        $this->maxTotalPrice = $item->max_total_price !== null ? (string) $item->max_total_price : '';
        $this->maxDeposit = $item->max_deposit !== null ? (string) $item->max_deposit : '';
        $this->readyToBookImmediately = (bool) $item->ready_to_book_immediately;
        $this->autoSendRequest = (bool) $item->auto_send_request;
        $this->notifyAvailable = (bool) $item->notify_available;
        $this->notifyPriceDrop = (bool) $item->notify_price_drop;
        $this->guestMessage = (string) $item->guest_message;
        $this->expiresAt = $item->expires_at?->format('Y-m-d\TH:i') ?? '';
    }

    public function save(WaitlistService $waitlist): void
    {
        $validated = $this->validate([
            'desiredCheckIn' => ['required', 'date', 'after_or_equal:today'],
            'desiredCheckOut' => ['required', 'date', 'after:desiredCheckIn'],
            'guestsCount' => ['required', 'integer', 'min:1', 'max:20'],
            'maxPricePerNight' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'maxTotalPrice' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'maxDeposit' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'readyToBookImmediately' => ['boolean'],
            'autoSendRequest' => ['boolean'],
            'notifyAvailable' => ['boolean'],
            'notifyPriceDrop' => ['boolean'],
            'guestMessage' => ['nullable', 'string', 'max:1000'],
            'expiresAt' => ['nullable', 'date', 'after:now'],
        ], attributes: [
            'desiredCheckIn' => __('waitlist.check_in'),
            'desiredCheckOut' => __('waitlist.check_out'),
            'guestsCount' => __('waitlist.guests_count'),
            'maxPricePerNight' => __('waitlist.max_price'),
            'maxTotalPrice' => __('waitlist.max_total_price'),
            'maxDeposit' => __('waitlist.max_deposit'),
            'readyToBookImmediately' => __('waitlist.ready_to_book'),
            'autoSendRequest' => __('waitlist.auto_send_request'),
            'notifyAvailable' => __('waitlist.notify_available'),
            'notifyPriceDrop' => __('waitlist.notify_price_drop'),
            'guestMessage' => __('waitlist.guest_message'),
            'expiresAt' => __('waitlist.expires_at'),
        ]);

        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $item = WaitlistItem::query()->where('user_id', $user->id)->findOrFail($this->waitlistItemId);

        try {
            $waitlist->update($user, $item, [
                'desired_check_in_date' => $validated['desiredCheckIn'],
                'desired_check_out_date' => $validated['desiredCheckOut'],
                'guests_count' => (int) $validated['guestsCount'],
                'max_price_per_night' => $this->floatOrNull($validated['maxPricePerNight'] ?? null),
                'max_total_price' => $this->floatOrNull($validated['maxTotalPrice'] ?? null),
                'max_deposit' => $this->floatOrNull($validated['maxDeposit'] ?? null),
                'ready_to_book_immediately' => (bool) $validated['readyToBookImmediately'],
                'auto_send_request' => (bool) $validated['autoSendRequest'],
                'notify_available' => (bool) $validated['notifyAvailable'],
                'notify_price_drop' => (bool) $validated['notifyPriceDrop'],
                'guest_message' => $validated['guestMessage'] ?: null,
                'expires_at' => $validated['expiresAt'] ?: null,
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($this->fieldNameForServiceError($field), $messages[0] ?? __('validation.invalid'));
            }

            return;
        }

        $this->saved = true;
        $this->dispatch('waitlist-updated');
    }

    public function render(): View
    {
        return view('livewire.waitlist.edit-waitlist-item-sheet');
    }

    private function floatOrNull(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function fieldNameForServiceError(string $field): string
    {
        return match ($field) {
            'desired_check_in_date' => 'desiredCheckIn',
            'desired_check_out_date' => 'desiredCheckOut',
            default => $field,
        };
    }
}
