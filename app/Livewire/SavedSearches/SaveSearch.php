<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use Livewire\Attributes\On;
use Livewire\Component;

class SaveSearch extends Component
{
    public string $city = '';

    public string $checkIn = '';

    public string $checkOut = '';

    public ?float $minPrice = null;

    public ?float $maxPrice = null;

    public string $bedType = '';

    public string $genderType = '';

    public string $name = '';

    public bool $showModal = false;

    #[On('save-current-search')]
    public function openWithParams(array $params): void
    {
        $this->city = $params['city'] ?? '';
        $this->checkIn = $params['check_in'] ?? '';
        $this->checkOut = $params['check_out'] ?? '';
        $this->minPrice = $params['min_price'] ?? null;
        $this->maxPrice = $params['max_price'] ?? null;
        $this->bedType = $params['bed_type'] ?? '';
        $this->genderType = $params['gender'] ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        SavedSearch::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'city' => $this->city ?: null,
            'check_in' => $this->checkIn ?: null,
            'check_out' => $this->checkOut ?: null,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'bed_type' => $this->bedType ?: null,
            'gender_type' => $this->genderType ?: null,
            'notify_email' => true,
            'notify_push' => false,
        ]);

        $this->showModal = false;
        $this->reset('name');
        session()->flash('success', 'Search saved!');
    }

    public function render()
    {
        return view('livewire.saved-searches.save-search');
    }
}
