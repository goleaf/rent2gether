<?php

namespace App\Livewire\Host\Listings\Steps;

use App\Models\Property;
use App\Models\User;
use App\Services\HostListings\Wizard\HostListingPublishService;
use App\Services\HostListings\Wizard\HostListingReadinessService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PublishStep extends Component
{
    #[Locked]
    public int $propertyId;

    public string $comment = '';

    public bool $readyConfirmation = false;

    public function mount(int $propertyId): void
    {
        $property = $this->ownedProperty($propertyId);

        $this->propertyId = $property->id;
        $this->comment = (string) $property->review_comment;
    }

    public function sendToReview(HostListingPublishService $publisher): void
    {
        $host = auth()->user();
        $property = $this->ownedProperty($this->propertyId);

        abort_unless($host instanceof User, 403);

        $validated = $this->validate([
            'readyConfirmation' => ['accepted'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'readyConfirmation' => __('listing_wizard.publish_step.ready'),
            'comment' => __('listing_wizard.publish_step.comment'),
        ]);

        $publisher->requestPublication(
            $host,
            $property,
            blank($validated['comment']) ? null : str($validated['comment'])->squish()->toString(),
        );

        $this->dispatch('listing-review-requested');
    }

    public function render(): View
    {
        $property = $this->ownedProperty($this->propertyId);
        $readiness = app(HostListingReadinessService::class)->checkPublishReadiness($property);

        return view('livewire.host.listings.steps.publish-step', [
            'property' => $property,
            'readiness' => $readiness,
            'reviewStatus' => $this->reviewStatus($property),
        ]);
    }

    private function reviewStatus(Property $property): string
    {
        $status = $property->review_status ?: 'not_requested';

        return __('listing_publish.review_statuses.'.$status);
    }

    private function ownedProperty(int $propertyId): Property
    {
        $property = Property::query()
            ->select([
                'id',
                'host_user_id',
                'user_id',
                'publication_status',
                'review_status',
                'review_comment',
                'rejection_reason',
                'review_requested_at',
            ])
            ->findOrFail($propertyId);

        $host = auth()->user();
        abort_unless($host instanceof User && $property->isOwnedBy($host), 403);

        return $property;
    }
}
