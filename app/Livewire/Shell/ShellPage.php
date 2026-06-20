<?php

namespace App\Livewire\Shell;

use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class ShellPage extends Component
{
    protected string $pageKey;

    protected ?string $actionRoute = null;

    protected array $actionParameters = [];

    public function render(): View
    {
        return view('livewire.shell.page', [
            'page' => $this->page(),
            'actionHref' => $this->actionHref(),
        ])->layout('layouts.app', [
            'title' => __('shell.pages.'.$this->pageKey.'.title'),
        ]);
    }

    protected function page(): array
    {
        $page = __('shell.pages.'.$this->pageKey);

        return is_array($page) ? $page : [];
    }

    protected function actionHref(): ?string
    {
        if ($this->actionRoute === null) {
            return null;
        }

        return route($this->actionRoute, [
            'locale' => app()->getLocale(),
            ...$this->actionParameters,
        ]);
    }
}
