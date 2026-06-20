<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use App\Models\User;
use App\Services\Favorites\FavoriteService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EditFavoriteNoteSheet extends Component
{
    public int $favoriteId;

    public string $note = '';

    public function mount(int $favoriteId): void
    {
        $favorite = Favorite::query()
            ->select(['id', 'user_id', 'note', 'personal_note'])
            ->where('user_id', auth()->id())
            ->findOrFail($favoriteId);

        $this->favoriteId = $favorite->id;
        $this->note = (string) ($favorite->personal_note ?: $favorite->note);
    }

    public function save(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'note' => __('favorites.personal_note'),
        ]);

        $favorites->updateNote($user, $this->favoriteId, $this->note);
        $this->dispatch('favorite-collections-changed');
    }

    public function render(): View
    {
        return view('livewire.favorites.edit-favorite-note-sheet');
    }
}
