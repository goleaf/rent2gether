<?php

namespace App\Livewire\Reviews;

use App\Models\Booking;
use App\Services\ReviewService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateReview extends Component
{
    #[Locked]
    public Booking $booking;

    #[Locked]
    public string $reviewType = 'guest';

    public int $overallRating = 5;

    public int $cleanlinessRating = 5;

    public int $safetyRating = 5;

    public int $locationRating = 5;

    public int $accuracyRating = 5;

    public int $bedComfortRating = 5;

    public int $communicationRating = 5;

    public int $valueRating = 5;

    public int $ruleComplianceRating = 5;

    public int $tidinessRating = 5;

    public int $punctualityRating = 5;

    public string $positiveComment = '';

    public string $negativeComment = '';

    public string $advice = '';

    public bool $wouldRecommend = true;

    public function mount(Booking $booking, string $type = 'guest'): void
    {
        $this->booking = $booking;
        $this->reviewType = $type;
    }

    public function submit(): void
    {
        $service = app(ReviewService::class);

        if ($this->reviewType === 'guest') {
            $service->createGuestReview(
                $this->booking,
                auth()->user(),
                [
                    'overall' => $this->overallRating,
                    'cleanliness' => $this->cleanlinessRating,
                    'safety' => $this->safetyRating,
                    'location' => $this->locationRating,
                    'accuracy' => $this->accuracyRating,
                    'bed_comfort' => $this->bedComfortRating,
                    'communication' => $this->communicationRating,
                    'value' => $this->valueRating,
                    'would_recommend' => $this->wouldRecommend,
                ],
                $this->positiveComment ?: null,
                $this->negativeComment ?: null,
                $this->advice ?: null,
            );
            $this->booking->update(['guest_review_left' => true]);
        } else {
            $service->createHostReview(
                $this->booking,
                auth()->user(),
                [
                    'overall' => $this->overallRating,
                    'rule_compliance' => $this->ruleComplianceRating,
                    'tidiness' => $this->tidinessRating,
                    'communication' => $this->communicationRating,
                    'punctuality' => $this->punctualityRating,
                    'would_recommend' => $this->wouldRecommend,
                ],
                $this->positiveComment ?: null,
            );
            $this->booking->update(['host_review_left' => true]);
        }

        session()->flash('success', 'Review submitted. Thank you!');
        $this->redirect(route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $this->booking]));
    }

    public function render(): View
    {
        return view('livewire.reviews.create-review');
    }
}
