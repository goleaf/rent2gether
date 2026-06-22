<?php

namespace App\Livewire\Reviews;

use App\Models\Review;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReviewList extends Component
{
    #[Computed]
    public function reviews(): CursorPaginator
    {
        return Review::query()
            ->select(['id', 'review_number', 'overall_rating', 'public_comment', 'published_at'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at')
            ->cursorPaginate(20);
    }

    public function render(): View
    {
        return view('livewire.reviews.review-list', [
            'reviews' => $this->reviews,
        ]);
    }
}
