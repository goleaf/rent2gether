<?php

namespace App\Livewire\Host\Reviews;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostReviewsPage extends Component
{
    public function render(): View
    {
        return view('livewire.host.reviews.host-reviews-page')->layout('layouts.app', [
            'title' => __('reviews.title'),
        ]);
    }
}
