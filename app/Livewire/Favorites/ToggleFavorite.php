<?php

namespace App\Livewire\Favorites;

use App\Models\Bed;
use App\Models\Favorite;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ToggleFavorite extends Component
{
    #[Locked]
    public int $bedId;

    public bool $isFavorited = false;

    public function mount(int $bedId): void
    {
        $this->bedId = $bedId;
        if (auth()->check()) {
            $this->isFavorited = auth()->user()->hasFavorited(Bed::find($bedId));
        }
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('auth.login'));

            return;
        }

        $user = auth()->user();

        if ($this->isFavorited) {
            Favorite::where('user_id', $user->id)->where('bed_id', $this->bedId)->delete();
            $this->isFavorited = false;
        } else {
            $bed = Bed::find($this->bedId);
            Favorite::create([
                'user_id' => $user->id,
                'bed_id' => $this->bedId,
                'price_at_save' => $bed?->price_per_night,
            ]);
            $this->isFavorited = true;
        }
    }

    public function render(): View
    {
        return view('livewire.favorites.toggle-favorite');
    }
}
