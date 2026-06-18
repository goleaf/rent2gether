<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SavedSearchesList extends Component
{
    #[Computed]
    public function searches()
    {
        return SavedSearch::where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    public function delete(int $id): void
    {
        SavedSearch::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        unset($this->searches);
    }

    public function toggleNotifications(int $id): void
    {
        $search = SavedSearch::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $search->update(['notify_email' => ! $search->notify_email]);
        unset($this->searches);
    }

    public function runSearch(int $id): void
    {
        $search = SavedSearch::findOrFail($id);
        $params = array_filter([
            'locale' => app()->getLocale(),
            'city' => $search->city,
            'check_in' => $search->check_in,
            'check_out' => $search->check_out,
            'min_price' => $search->min_price,
            'max_price' => $search->max_price,
            'bed_type' => $search->bed_type,
            'gender' => $search->gender_type,
        ]);

        $this->redirect(route('search.index', $params));
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-searches-list');
    }
}
