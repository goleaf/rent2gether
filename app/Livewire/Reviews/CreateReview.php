<?php

namespace App\Livewire\Reviews;

use App\Enums\ReviewType;
use App\Models\Booking;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CreateReview extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $bookingId;

    #[Locked]
    public string $reviewType = 'guest_to_place';

    public int $overallRating = 5;

    public int $cleanlinessRating = 5;

    public int $safetyRating = 5;

    public int $locationRating = 5;

    public int $accuracyRating = 5;

    public int $sleepingPlaceComfortRating = 5;

    public int $amenitiesRating = 5;

    public int $hostCommunicationRating = 5;

    public int $neighborsRating = 5;

    public int $valueRating = 5;

    public int $ruleFollowingRating = 5;

    public int $communicationRating = 5;

    public int $punctualityRating = 5;

    public int $respectRating = 5;

    public string $likedText = '';

    public string $improvementText = '';

    public string $adviceText = '';

    public string $hostComment = '';

    public bool $recommend = true;

    public bool $recommendGuest = true;

    /**
     * @var list<TemporaryUploadedFile>
     */
    public array $photos = [];

    public function mount(Booking $booking, ?string $type = null): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $this->bookingId = $booking->id;
        $this->reviewType = $this->resolveReviewType($booking, $user, $type);
    }

    public function submit(ReviewService $reviews): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $booking = $this->booking();

        if ($this->reviewType === ReviewType::HostToGuest->value) {
            $validated = $this->validate($this->hostRules(), [], $this->validationAttributes());

            $reviews->createHostReview(
                booking: $booking,
                host: $user,
                ratings: [
                    'overall' => $validated['overallRating'],
                    'rule_following' => $validated['ruleFollowingRating'],
                    'cleanliness' => $validated['cleanlinessRating'],
                    'communication' => $validated['communicationRating'],
                    'punctuality' => $validated['punctualityRating'],
                    'respect' => $validated['respectRating'],
                ],
                comment: $validated['hostComment'] ?: null,
                recommendGuest: (bool) $validated['recommendGuest'],
            );

            session()->flash('success', __('notifications.flash.review_submitted'));
            $this->redirectRoute('host.bookings.manage', [
                'locale' => app()->getLocale(),
                'booking' => $booking,
            ], navigate: true);

            return;
        }

        $validated = $this->validate($this->guestRules(), [], $this->validationAttributes());

        $photoPaths = collect($validated['photos'] ?? [])
            ->map(fn (TemporaryUploadedFile $photo): string => $photo->store('review-photos', 'public'))
            ->values()
            ->all();

        $reviews->createGuestReview(
            booking: $booking,
            guest: $user,
            ratings: [
                'overall' => $validated['overallRating'],
                'cleanliness' => $validated['cleanlinessRating'],
                'safety' => $validated['safetyRating'],
                'location' => $validated['locationRating'],
                'accuracy' => $validated['accuracyRating'],
                'sleeping_place_comfort' => $validated['sleepingPlaceComfortRating'],
                'amenities' => $validated['amenitiesRating'],
                'host_communication' => $validated['hostCommunicationRating'],
                'neighbors' => $validated['neighborsRating'],
                'value' => $validated['valueRating'],
            ],
            likedText: $validated['likedText'] ?: null,
            improvementText: $validated['improvementText'] ?: null,
            adviceText: $validated['adviceText'] ?: null,
            recommend: (bool) $validated['recommend'],
            photos: $photoPaths,
        );

        session()->flash('success', __('notifications.flash.review_submitted'));
        $this->redirectRoute('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $booking,
        ], navigate: true);
    }

    public function render(): View
    {
        $booking = $this->booking();

        return view('livewire.reviews.create-review', [
            'booking' => $booking,
            'isHostReview' => $this->reviewType === ReviewType::HostToGuest->value,
            'placeTitle' => $this->placeTitle($booking),
            'ratingOptions' => $this->ratingOptions(),
            'guestRatings' => $this->guestRatings(),
            'hostRatings' => $this->hostRatings(),
        ])->layout('layouts.app', [
            'title' => $this->reviewType === ReviewType::HostToGuest->value
                ? __('booking.review.host_title')
                : __('booking.review.guest_title'),
        ]);
    }

    private function resolveReviewType(Booking $booking, User $user, ?string $type): string
    {
        $requestedType = $type ?: request()->query('type');

        if ($requestedType === ReviewType::HostToGuest->value || request()->routeIs('host.reviews.create')) {
            abort_unless((int) $booking->host_user_id === (int) $user->id, 403);

            return ReviewType::HostToGuest->value;
        }

        abort_unless((int) $booking->guest_user_id === (int) $user->id, 403);

        return ReviewType::GuestToPlace->value;
    }

    private function booking(): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'guest_id',
                'guest_user_id',
                'host_id',
                'host_user_id',
                'bed_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'guest_review_left',
                'host_review_left',
                'review_deadline_at',
            ])
            ->with([
                'guest:id,name',
                'host:id,name',
                'bed:id,title',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->findOrFail($this->bookingId);
    }

    /**
     * @return array<string, list<string>>
     */
    private function guestRules(): array
    {
        return [
            'overallRating' => ['required', 'integer', 'between:1,5'],
            'cleanlinessRating' => ['required', 'integer', 'between:1,5'],
            'safetyRating' => ['required', 'integer', 'between:1,5'],
            'locationRating' => ['required', 'integer', 'between:1,5'],
            'accuracyRating' => ['required', 'integer', 'between:1,5'],
            'sleepingPlaceComfortRating' => ['required', 'integer', 'between:1,5'],
            'amenitiesRating' => ['required', 'integer', 'between:1,5'],
            'hostCommunicationRating' => ['required', 'integer', 'between:1,5'],
            'neighborsRating' => ['required', 'integer', 'between:1,5'],
            'valueRating' => ['required', 'integer', 'between:1,5'],
            'likedText' => ['nullable', 'string', 'max:1200'],
            'improvementText' => ['nullable', 'string', 'max:1200'],
            'adviceText' => ['nullable', 'string', 'max:1200'],
            'recommend' => ['boolean'],
            'photos' => ['array', 'max:4'],
            'photos.*' => ['image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function hostRules(): array
    {
        return [
            'overallRating' => ['required', 'integer', 'between:1,5'],
            'ruleFollowingRating' => ['required', 'integer', 'between:1,5'],
            'cleanlinessRating' => ['required', 'integer', 'between:1,5'],
            'communicationRating' => ['required', 'integer', 'between:1,5'],
            'punctualityRating' => ['required', 'integer', 'between:1,5'],
            'respectRating' => ['required', 'integer', 'between:1,5'],
            'hostComment' => ['nullable', 'string', 'max:1200'],
            'recommendGuest' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return app('translator')->get('booking.review.validation_attributes');
    }

    private function placeTitle(Booking $booking): string
    {
        $translation = $booking->sleepingPlace?->translations
            ?->firstWhere('locale', app()->getLocale());

        return $translation?->title
            ?: $booking->sleepingPlace?->display_name
            ?: $booking->sleepingPlace?->place_number
            ?: $booking->bed?->title
            ?: __('booking.payment_page.summary.unnamed_place');
    }

    /**
     * @return list<int>
     */
    private function ratingOptions(): array
    {
        return [5, 4, 3, 2, 1];
    }

    /**
     * @return array<string, string>
     */
    private function guestRatings(): array
    {
        return [
            'overallRating' => 'overall_rating',
            'cleanlinessRating' => 'cleanliness_rating',
            'safetyRating' => 'safety_rating',
            'locationRating' => 'location_rating',
            'accuracyRating' => 'accuracy_rating',
            'sleepingPlaceComfortRating' => 'sleeping_place_comfort_rating',
            'amenitiesRating' => 'amenities_rating',
            'hostCommunicationRating' => 'host_communication_rating',
            'neighborsRating' => 'neighbors_rating',
            'valueRating' => 'value_rating',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function hostRatings(): array
    {
        return [
            'overallRating' => 'overall_rating',
            'ruleFollowingRating' => 'rule_following_rating',
            'cleanlinessRating' => 'guest_cleanliness_rating',
            'communicationRating' => 'communication_rating',
            'punctualityRating' => 'punctuality_rating',
            'respectRating' => 'respect_rating',
        ];
    }
}
