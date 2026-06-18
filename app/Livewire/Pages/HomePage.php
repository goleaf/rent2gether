<?php

namespace App\Livewire\Pages;

use Illuminate\View\View;
use Livewire\Component;

class HomePage extends Component
{
    public function render(): View
    {
        return view('livewire.pages.home-page')
            ->layout('layouts.app', ['title' => __('app.home_title')]);
    }
}
