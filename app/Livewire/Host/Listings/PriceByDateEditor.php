<?php

namespace App\Livewire\Host\Listings;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PriceByDateEditor extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $start = '';

    public string $end = '';

    public float $price = 0;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->start = now()->addDay()->toDateString();
        $this->end = now()->addDays(8)->toDateString();
    }

    public function save(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $place = SleepingPlace::query()->findOrFail($this->sleepingPlaceId);

        abort_unless($host instanceof User, 403);

        $validated = $this->validatedPayload();

        $calendar->setPriceForDates($host, $place, ['start' => $validated['start'], 'end' => $validated['end']], $validated['price']);
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.price-by-date-editor');
    }

    /**
     * @return array{start:string,end:string,price:float}
     */
    private function validatedPayload(): array
    {
        $validated = $this->validate([
            'start' => ['required', 'date', 'after_or_equal:today'],
            'end' => ['required', 'date', 'after:start'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ], attributes: [
            'start' => __('listing_calendar.fields.start_date'),
            'end' => __('listing_calendar.fields.end_date'),
            'price' => __('listing_calendar.fields.price'),
        ]);

        $start = CarbonImmutable::parse($validated['start']);
        $end = CarbonImmutable::parse($validated['end']);

        if ($start->diffInDays($end) > 366) {
            throw ValidationException::withMessages([
                'end' => __('listing_calendar.errors.range_too_large'),
            ]);
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'price' => (float) $validated['price'],
        ];
    }
}
