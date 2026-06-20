<?php

namespace App\Livewire\Shell;

use Illuminate\Contracts\View\View;

class FavoritesPage extends ShellPage
{
    protected string $pageKey = 'guest.favorites';

    protected ?string $actionRoute = 'search.index';

    public function render(): View
    {
        return view('livewire.shell.favorites-page', [
            'pageKey' => $this->pageKey,
            'actionHref' => $this->actionHref(),
        ])->layout('layouts.app', [
            'title' => __('shell.pages.'.$this->pageKey.'.title'),
        ]);
    }
}
