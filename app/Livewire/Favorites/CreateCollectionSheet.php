<?php

namespace App\Livewire\Favorites;

use App\Models\FavoriteCollection;
use App\Models\User;
use App\Services\Favorites\FavoriteCollectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateCollectionSheet extends Component
{
    public string $title = '';

    public string $description = '';

    public string $icon = 'heart';

    public string $color = 'emerald';

    public function create(FavoriteCollectionService $collections): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:80', Rule::in(FavoriteCollection::allowedIcons())],
            'color' => ['nullable', 'string', 'max:40', Rule::in(FavoriteCollection::allowedColors())],
        ], attributes: [
            'title' => __('favorites.fields.title'),
            'description' => __('favorites.fields.description'),
            'icon' => __('favorites.fields.icon'),
            'color' => __('favorites.fields.color'),
        ]);

        $collections->create($user, [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'icon' => $this->icon,
            'color' => $this->color,
        ]);

        $this->reset(['title', 'description']);
        $this->dispatch('favorite-collections-changed');
    }

    public function render(): View
    {
        return view('livewire.favorites.create-collection-sheet');
    }
}
