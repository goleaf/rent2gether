<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CalendarBulkEditor extends Component
{
    #[Locked]
    public int $propertyId;

    public string $start = '';

    public string $end = '';

    public ?float $price = null;

    public ?int $minNights = null;

    public ?int $maxNights = null;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
        $this->start = now()->addDay()->toDateString();
        $this->end = now()->addDays(8)->toDateString();
    }

    public function openDates(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $property = Property::query()->findOrFail($this->propertyId);

        abort_unless($host instanceof User, 403);

        $validated = $this->validatedPayload();

        $calendar->bulkOpenDates($host, $property, ['start' => $validated['start'], 'end' => $validated['end']], [
            'price' => $validated['price'],
            'min_nights' => $validated['minNights'],
            'max_nights' => $validated['maxNights'],
        ]);
        $this->dispatch('listing-step-saved');
    }

    public function closeDates(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $property = Property::query()->findOrFail($this->propertyId);

        abort_unless($host instanceof User, 403);

        $validated = $this->validatedPayload();

        $calendar->bulkCloseDates($host, $property, ['start' => $validated['start'], 'end' => $validated['end']], 'host_blocked');
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.calendar-bulk-editor');
    }

    /**
     * @return array{start:string,end:string,price:float|null,minNights:int|null,maxNights:int|null}
     */
    private function validatedPayload(): array
    {
        $validated = $this->validate([
            'start' => ['required', 'date', 'after_or_equal:today'],
            'end' => ['required', 'date', 'after:start'],
            'price' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
            'minNights' => ['nullable', 'integer', 'min:1', 'max:365'],
            'maxNights' => ['nullable', 'integer', 'min:1', 'max:365'],
        ], attributes: $this->validationAttributes());

        $start = CarbonImmutable::parse($validated['start']);
        $end = CarbonImmutable::parse($validated['end']);

        if ($start->diffInDays($end) > 366) {
            throw ValidationException::withMessages([
                'end' => __('listing_calendar.errors.range_too_large'),
            ]);
        }

        if ($validated['minNights'] !== null && $validated['maxNights'] !== null && (int) $validated['maxNights'] < (int) $validated['minNights']) {
            throw ValidationException::withMessages([
                'maxNights' => __('listing_calendar.errors.max_less_than_min'),
            ]);
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'price' => blank($validated['price']) ? null : (float) $validated['price'],
            'minNights' => blank($validated['minNights']) ? null : (int) $validated['minNights'],
            'maxNights' => blank($validated['maxNights']) ? null : (int) $validated['maxNights'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'start' => __('listing_calendar.fields.start_date'),
            'end' => __('listing_calendar.fields.end_date'),
            'price' => __('listing_calendar.fields.price'),
            'minNights' => __('listing_calendar.fields.min_nights'),
            'maxNights' => __('listing_calendar.fields.max_nights'),
        ];
    }
}
