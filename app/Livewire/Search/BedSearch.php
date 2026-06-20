<?php

namespace App\Livewire\Search;

use App\Enums\BedType;
use App\Enums\GenderType;
use App\Models\Bed;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BedSearch extends Component
{
    use WithPagination;

    #[Url(as: 'city')]
    public string $city = '';

    #[Url(as: 'in')]
    public string $checkIn = '';

    #[Url(as: 'out')]
    public string $checkOut = '';

    #[Url(as: 'max')]
    public string $priceMax = '';

    #[Url(as: 'type')]
    public string $bedType = '';

    #[Url(as: 'gender')]
    public string $genderType = '';

    #[Url(as: 'instant')]
    public bool $instantOnly = false;

    #[Url(as: 'locker')]
    public bool $hasLocker = false;

    #[Url(as: 'wifi')]
    public bool $hasWifi = false;

    #[Url(as: 'sort')]
    public string $sort = 'price_asc';

    public function updatedCity(): void
    {
        $this->resetPage();
    }

    public function updatedCheckIn(): void
    {
        $this->resetPage();
    }

    public function updatedCheckOut(): void
    {
        $this->resetPage();
    }

    public function updatedPriceMax(): void
    {
        $this->resetPage();
    }

    public function updatedBedType(): void
    {
        $this->resetPage();
    }

    public function updatedGenderType(): void
    {
        $this->resetPage();
    }

    public function updatedInstantOnly(): void
    {
        $this->resetPage();
    }

    public function updatedHasLocker(): void
    {
        $this->resetPage();
    }

    public function updatedHasWifi(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function nights(): int
    {
        if ($this->checkIn && $this->checkOut) {
            try {
                return (int) now()->parse($this->checkIn)->diffInDays($this->checkOut);
            } catch (\Throwable) {
                return 0;
            }
        }

        return 0;
    }

    #[Computed]
    public function beds()
    {
        $query = Bed::query()
            ->active()
            ->with(['room.property.cardMedia'])
            ->whereHas('room', fn (Builder $q) => $q->active()
                ->whereHas('property', fn (Builder $pq) => $pq->active()
                    ->when($this->city, fn ($q) => $q->where('city', 'like', '%'.$this->city.'%'))
                )
            );

        if ($this->checkIn && $this->checkOut && $this->nights > 0) {
            $query->availableForPeriod($this->checkIn, $this->checkOut);
            $query->where('min_nights', '<=', $this->nights);
            $query->where(fn ($q) => $q->whereNull('max_nights')->orWhere('max_nights', '>=', $this->nights));
        }

        if ($this->priceMax !== '') {
            $query->where('price_per_night', '<=', (float) $this->priceMax);
        }

        if ($this->bedType !== '') {
            $query->where('type', $this->bedType);
        }

        if ($this->genderType !== '') {
            $query->whereHas('room', fn (Builder $q) => $q
                ->where('gender_type', $this->genderType)
                ->orWhere('gender_type', GenderType::Mixed->value)
            );
        }

        if ($this->instantOnly) {
            $query->where('instant_book', true);
        }

        if ($this->hasLocker) {
            $query->where('has_locker', true);
        }

        if ($this->hasWifi) {
            $query->whereHas('room.property', fn (Builder $q) => $q->whereJsonContains('amenities', 'wifi'));
        }

        $query->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price_per_night'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('price_per_night'));

        return $query->simplePaginate(12);
    }

    public function bedTypeOptions(): array
    {
        return collect(BedType::cases())->mapWithKeys(
            fn (BedType $t) => [$t->value => $t->label()]
        )->all();
    }

    public function genderOptions(): array
    {
        return collect(GenderType::cases())->mapWithKeys(
            fn (GenderType $g) => [$g->value => $g->label()]
        )->all();
    }

    public function render(): View
    {
        return view('livewire.search.bed-search');
    }
}
