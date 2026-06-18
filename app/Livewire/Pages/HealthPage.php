<?php

namespace App\Livewire\Pages;

use Illuminate\View\View;
use Livewire\Component;

class HealthPage extends Component
{
    public function render(): View
    {
        return view('livewire.pages.health-page')
            ->layout('layouts.app', ['title' => __('app.health_title')]);
    }
}
